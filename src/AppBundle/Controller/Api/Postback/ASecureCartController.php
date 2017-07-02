<?php

namespace AppBundle\Controller\Api\Postback;

use AppBundle\Entity\Badge;
use AppBundle\Entity\Registration;
use AppBundle\Entity\Registrationerror;
use AppBundle\Entity\Registrationextra;
use AppBundle\Entity\Registrationshirt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ASecureCartController extends Controller
{
    /**
     * @Route("/api/postback/asecurecart")
     *
     * @param Request $request
     * @return Response
     */
    public function cartPostBack(Request $request)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $key = $this->getParameter('postback.sha1_key.asecurecart');
        $xmlPost = '';
        if ($request->request->has('XML')) {
            $xmlPost = $request->request->get('XML');
        }
        $postBackSignature = '';
        if ($request->request->has('PostbackSignature')) {
            $postBackSignature = $request->request->get('PostbackSignature');
        }

        $generatedHash = base64_encode(hash_hmac('sha1', $xmlPost, $key, true));
        if ($generatedHash != $postBackSignature) {
            $error = "Postback Signature didn't match. Generated: '" . $generatedHash
                . "', Postback: '" . $postBackSignature . "'";
            $this->createRegistrationError($error, $xmlPost);

            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        $xml = new \SimpleXMLElement($xmlPost);

        $cartItems = $xml->cart->cart_items->cart_item;
        foreach ($cartItems as $cartItem) {
            /* @var $cartItem \SimpleXMLElement */

            $attributes = $cartItem->attributes();

            $regType = (String)$attributes['id'];
            if (strpos($regType, 'OUTREACH') !== false) {
                //This is a donation, not a registration, skipping
                continue;
            }

            if (strpos($regType, 'ADREGSTANDARD') !== false) {
                $regType = 'ADREGSTANDARD';
            }

            $badgeType = $this->get('repository_badgetype')->getBadgeTypeFromType($regType);
            if (!$badgeType) {
                $error = "BadgeType didn't load correctly: '" . $regType . "'";
                $this->createRegistrationError($error, $xmlPost);

                continue;
            }

            $badgeStatus = $this->get('repository_badgestatus')->getBadgeStatusFromStatus('NEW');
            if (!$badgeType) {
                $error = "BadgeStatus 'NEW' didn't exist. Configuration Error.";
                $this->createRegistrationError($error, $xmlPost);

                continue;
            }

            $registrationType = $this->get('repository_registrationtype')->getRegistrationTypeFromType('Online');
            if (!$registrationType) {
                $error = "RegistrationType 'Online' didn't exist. Configuration Error.";
                $this->createRegistrationError($error, $xmlPost);

                continue;
            }

            $registrationStatus = $this->get('repository_registrationstatus')->getRegistrationStatusFromStatus('New');
            if (!$registrationStatus) {
                $error = "RegistrationStatus 'New' didn't exist. Configuration Error.";
                $this->createRegistrationError($error, $xmlPost);

                continue;
            }

            $event = $this->get('repository_event')->getCurrentEvent();
            if (!$event) {
                $error = "Could not load current event.";
                $this->createRegistrationError($error, $xmlPost);

                continue;
            }

            // Addon1 First | Middle | Last
            // Addon2 Badge Name
            // Addon3 email
            // Addon4 Birthday
            // Addon5
            // Addon6 Mens/Womens Shirt
            // size   Shirt Size
            // Addon9 SponsorBreakfast Extra 'SponsorBreakfast' or 'Decline' or '' if not a sponsor type

            // hAddon1 Address 1
            // hAddon2 Adrress 2
            // hAddon3 City | State | zip
            // hAddon4 phone
            // hAddon5 Contact Newsletter
            // hAddon6 Contact Volunteer
            $registration = new Registration();
            $registration->setXml((String)$xmlPost);
            $registration->setEvent($event);
            $registration->setRegistrationstatus($registrationStatus);
            $registration->setRegistrationtype($registrationType);
            $name = explode('|', (String)$attributes['addon1']);
            $registration->setFirstname(trim($name[0]));
            $registration->setMiddlename(trim($name[1]));
            $registration->setLastname(trim($name[2]));
            $registration->setBadgename(trim((String)$attributes['addon2']));
            $registration->setEmail(trim((String)$attributes['addon3']));

            $birthDate = (String)$attributes['addon4'];
            if (!strtotime($birthDate)) {
                $birthDate = str_replace('-', '/', $birthDate);
            }

            $registration->setBirthday(new \DateTime($birthDate));
            $registration->setAddress((String)$attributes['haddon1']);
            $registration->setAddress2((String)$attributes['haddon2']);
            $address = explode('|', (String)$attributes['haddon3']);
            $registration->setCity(trim($address[0]));
            $registration->setState(trim($address[1]));
            $registration->setZip(trim($address[2]));
            $registration->setPhone((String)$attributes['haddon4']);
            $registration->setContactNewsletter((bool)$attributes['haddon5']);
            $registration->setContactVolunteer((bool)$attributes['haddon6']);
            $number = $this->get('repository_registration')->generateNumber($registration);
            $registration->setNumber($number);

            if ($badgeType->getName() == 'ADREGSTANDARD' &&
                ($registration->getBirthday()->getTimestamp() > strtotime($event->getStartdate()->format('m/d/y') . " -18 years"))
            ) {
                $badgeType = $this->get('repository_badgetype')->getBadgeTypeFromType('MINOR');
                if (!$badgeType) {
                    $error = "BadgeType didn't load correctly: 'MINOR'";
                    $this->createRegistrationError($error, $xmlPost);

                    continue;
                }
            }

            $entityManager->persist($registration);
            $entityManager->flush();

            $Badge = new Badge();
            $badgeNumber = $this->get('repository_badge')->generateNumber();
            $Badge->setNumber($badgeNumber);
            $Badge->setBadgetype($badgeType);
            $Badge->setBadgestatus($badgeStatus);
            $Badge->setRegistration($registration);
            $entityManager->persist($Badge);

            $shirt_type = explode(' ', (String)$attributes['addon6']);
            if (array_key_exists(0, $shirt_type)
                && $shirt_type[0] != ''
                && (String)$attributes['size'] != ''
            ) {
                $shirtType = $shirt_type[0];
                $shirtSize = (String)$attributes['size'];
                $shirt = $this->get('repository_shirt')->getShirtFromTypeAndSize($shirtType, $shirtSize);
                if ($shirt) {
                    $registrationShirt = new Registrationshirt();
                    $registrationShirt->setRegistration($registration);
                    $registrationShirt->setShirt($shirt);
                    $entityManager->persist($registrationShirt);
                } else {
                    $error = 'Shirt Couldn\'t be applied to registration! ' . $registration->getNumber() . ' '
                        . (String)$attributes['addon6'] . ' ' . (String)$attributes['size'];
                    $this->createRegistrationError($error, $xmlPost);
                }
            }

            $breakfastOption = (String)$attributes['addon5'];
            if (strpos($breakfastOption, 'SponsorBreakfast') !== false) {
                $extra = $this->get('repository_extra')->getExtraFromName('SponsorBreakfast');
                $registrationExtra = new Registrationextra();
                $registrationExtra->setRegistration($registration);
                $registrationExtra->setExtra($extra);
                $entityManager->persist($registrationExtra);
            }

            $entityManager->flush();

            $badges = $this->get('repository_badge')->getBadgesFromRegistration($registration);
            $this->get('repository_registration')->sendConfirmationEmail($registration, $badges);
        }
        $response = new Response();

        return $response;
    }
    /*
    Example Postback from ASecureCart
    <?xml version="1.0" encoding="UTF-8"?>
    <carts>
       <cart>
          <order_info number="38416" date="5/4/2014" time="13:04" ip_address="75.73.165.142" orderid="68cfc483-ceac-4e8e-800b-a038d2b50fbc" />
          <ordered_by name="John Koniges" company="" address="123 Main St." address2="" city="Minnesota City" state="MN" province="" zip="55122" country="United States" phone="612-555-1122" extension="" fax="" email="john.koniges@animedetour.com" />
          <deliver_to name="John Koniges" company="" address="123 Main St." address2="" city="Minnesota City" state="MN" province="" zip="55122" country="United States" residential="" phone="612-555-1122" extension="" />
          <cart_items>
             <cart_item id="JJKTEST" describe="This is a test of the posting." onetime="0" color="" size="3XL" addon1="addon1" addon2=""
             addon3="addon2" addon4="" addon5="" addon6="" addon7="" addon8="" naddon1="" naddon2=""
             haddon1="John|J|Koniges, 123 Main St.||Minnesota City|MN|55122|john.koniges@animedetour.com|6125551122" haddon2="JohnK" haddon3=""
             haddon4="08/19/1983" haddon5="1" haddon6="1" haddon7="" haddon8="" sku="REG=3XL|" eventstart="05/01/2014 00:00:00 AM"
             eventend="05/31/2014 00:00:00 AM" qty="1" unit_price="1.00" item_total="1.00"
             />
          </cart_items>
          <charges subtotal="1.00" subtotal_after_discount="1.00" subtotal_after_coupon="1.00" subtotal_after_shipping="1.00" subtotal_after_taxes="1.00" fee="0.00" subtotal_after_fee="1.00" grand_total="1.00">
             <global_discount_details amount="0.00" message="" />
             <coupon_details coupon="" value="" text="Coupon" amount="0.00" />
             <shipping_details text="Shipping" region="" shipping_weight="0" free_shipping_message="" shipping_amount="0.00" />
             <tax_details text="" region="" value="0" tax_amount="0.00" />
             <gift_certificate_details certficiates="" amounts="" applied="0" />
          </charges>
          <payment_information method_id="3" method="Credit Card" name="John J Koniges" address="123 Main St." city="Minnesota City" state="MN" province="" zip="55122" country="United States" credit_card="Visa" authorization_code="004534" authorization_message="This transaction has been approved." transaction_key="6147646417" recurring_profile_id="" />
       </cart>
    </carts>
     */

    /**
     * @param String $error
     * @param String $xmlPost
     */
    protected function createRegistrationError($error, $xmlPost)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $registrationError = new Registrationerror();
        $registrationError->setDescription($error);
        $registrationError->setXml($xmlPost);
        $this->sendErrorEmail($registrationError);
        $entityManager->persist($registrationError);
        $entityManager->flush();
    }

    /**
     * @param Registrationerror $registrationError
     */
    protected function sendErrorEmail($registrationError)
    {
        $event = $this->get('repository_event')->getCurrentEvent();

        $message = \Swift_Message::newInstance()
            ->setSubject("Anime Detour {$event->getYear()} Registration Error")
            ->setFrom('it@animedetour.com', 'Anime Detour IT')
            ->setTo('it@animedetour.com')
            ->setSender('it@animedetour.com')
            ->setBody(
                $registrationError->getDescription(),
                'text/html'
            )
        ;
        $mailer = $this->get('swiftmailer.mailer');
        $mailer->send($message);
    }
}

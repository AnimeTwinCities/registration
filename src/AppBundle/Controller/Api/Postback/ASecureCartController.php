<?php
/**
 * Copyright (c) 2019. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Api\Postback;

use AppBundle\Entity\Badge;
use AppBundle\Entity\BadgeStatus;
use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Extra;
use AppBundle\Entity\Registration;
use AppBundle\Entity\RegistrationError;
use AppBundle\Entity\Registrationshirt;
use AppBundle\Entity\RegistrationStatus;
use AppBundle\Entity\RegistrationType;
use AppBundle\Entity\Shirt;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ASecureCartController extends Controller
{
    /**
     * @Route("/api/postback/asecurecart", name="api_postback_asecurecart")
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

        try {
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

                /** @var BadgeType $badgeType */
                $badgeType = $entityManager
                    ->getRepository(BadgeType::class)
                    ->getBadgeTypeFromType($regType);
                if (!$badgeType) {
                    $error = "BadgeType didn't load correctly: '" . $regType . "'";
                    $this->createRegistrationError($error, $xmlPost);

                    continue;
                }

                $badgeStatus = $entityManager
                    ->getRepository(BadgeStatus::class)
                    ->getBadgeStatusFromStatus('NEW');
                if (!$badgeType) {
                    $error = "BadgeStatus 'NEW' didn't exist. Configuration Error.";
                    $this->createRegistrationError($error, $xmlPost);

                    continue;
                }

                $registrationType = $entityManager
                    ->getRepository(RegistrationType::class)
                    ->getRegistrationTypeFromType('Online');
                if (!$registrationType) {
                    $error = "RegistrationType 'Online' didn't exist. Configuration Error.";
                    $this->createRegistrationError($error, $xmlPost);

                    continue;
                }

                $registrationStatus = $entityManager
                    ->getRepository(RegistrationStatus::class)
                    ->getRegistrationStatusFromStatus('New');
                if (!$registrationStatus) {
                    $error = "RegistrationStatus 'New' didn't exist. Configuration Error.";
                    $this->createRegistrationError($error, $xmlPost);

                    continue;
                }

                /** @var Event $event */
                $event = $entityManager->getRepository(Event::class)->getCurrentEvent();
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

                // hAddon1 Address 1
                // hAddon2 Adrress 2
                // hAddon3 City | State | zip
                // hAddon4 phone
                // hAddon5 Contact Newsletter
                // hAddon6 Contact Volunteer
                $registration = new Registration();
                $registration->setXml((String)$xmlPost);
                $registration->setEvent($event);
                $registration->setRegistrationStatus($registrationStatus);
                $registration->setRegistrationType($registrationType);
                $name = explode('|', (String)$attributes['addon1']);
                $registration->setFirstName(trim($name[0]));
                $registration->setMiddleName(trim($name[1]));
                $registration->setLastName(trim($name[2]));
                $badgeName = trim((String)$attributes['addon2']);
                $badgeName = substr($badgeName, 0, 20);
                if (!$badgeName) {
                    $badgeName = $registration->getFirstName();
                }
                $registration->setBadgeName($badgeName);
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
                $contactNewsletter = (String)$attributes['haddon5'];
                $registration->setContactNewsletter((bool)$contactNewsletter);
                $contactVolunteer = (String)$attributes['haddon5'];
                $registration->setContactVolunteer((bool)$contactVolunteer);
                $number = $entityManager
                    ->getRepository(Registration::class)
                    ->generateNumber($registration);
                $registration->setNumber($number);

                $entityManager->persist($registration);
                $entityManager->flush();

                $Badge = new Badge();
                $badgeNumber = $entityManager->getRepository(Badge::class)->generateNumber();
                $Badge->setNumber($badgeNumber);
                $Badge->setBadgeType($badgeType);
                $Badge->setBadgeStatus($badgeStatus);
                $Badge->setRegistration($registration);
                $entityManager->persist($Badge);

                $shirt_type = explode(' ', (String)$attributes['addon6']);
                if (array_key_exists(0, $shirt_type)
                    && $shirt_type[0] != ''
                    && (String)$attributes['size'] != ''
                ) {
                    $shirtType = $shirt_type[0];
                    $shirtSize = (String)$attributes['size'];
                    $shirt = $entityManager
                        ->getRepository(Shirt::class)
                        ->getShirtFromTypeAndSize($shirtType, $shirtSize);
                    if ($shirt) {
                        $registrationShirt = new RegistrationShirt();
                        $registrationShirt->setRegistration($registration);
                        $registrationShirt->setShirt($shirt);
                        $entityManager->persist($registrationShirt);
                    } else {
                        $error = 'Shirt Couldn\'t be applied to registration! ' . $registration->getNumber() . ' '
                            . (String)$attributes['addon6'] . ' ' . (String)$attributes['size'];
                        $this->createRegistrationError($error, $xmlPost);
                    }
                }

                $entityManager->flush();

                $this->get('util_email')->generateAndSendConfirmationEmail($registration);
            }
        } catch (ORMException $e) {
            $this->get('util_email')->sendErrorMessageToRegistration($e->getMessage());
        } catch (DriverException $e) {
            // Catch truncate errors
            $this->get('util_email')->sendErrorMessageToRegistration($e->getMessage());
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
     * @throws ORMException
     */
    protected function createRegistrationError($error, $xmlPost)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $registrationError = new RegistrationError();
        $registrationError->setDescription($error);
        $registrationError->setXml($xmlPost);
        $this->get('util_email')->sendErrorMessageToRegistration($registrationError);
        $entityManager->persist($registrationError);
        $entityManager->flush();
    }
}

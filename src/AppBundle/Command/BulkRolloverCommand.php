<?php
/**
 * Copyright (c) 2020. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Command;

use AppBundle\Entity\Badge;
use AppBundle\Entity\Event;
use AppBundle\Entity\Group;
use AppBundle\Entity\History;
use AppBundle\Entity\Registration;
use AppBundle\Entity\RegistrationShirt;
use AppBundle\Entity\RegistrationStatus;
use AppBundle\Entity\RegistrationType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;

class BulkRolloverCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:rollover-bulk-2020';
    /** @var bool */
    protected $confirmSend = false;

    public function __construct(bool $confirmSend = false)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        $this->confirmSend = $confirmSend;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Rolls over all registrations in 2020 and sends them a confirmation email for 2021.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Allows bulk rollover all registrations not refunded or picked up')


            ->addArgument('password', $this->confirmSend ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'Should we send this')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $doctrine = $this->getContainer()->get('doctrine');

        /** @var Registration[] $registrations */
        $registrations = $doctrine
            ->getRepository(Registration::class)
            ->findActiveRegistrations();


        $output->writeln([
            '2020 Rollover Script',
            '============',
            'Found ' . count($registrations) . ' registrations',
            '',
            'Starting rollovers and confirmation email sending: ',
        ]);

        $sent = 0;
        $skipped = 0;
        $groups = 0;
        $missingEmail = 0;
        $errors = [];
        foreach ($registrations as $registration) {
            $didRollover = false;
            try {
                $didRollover = $this->rolloverRegistration($registration);
            } catch (\Exception $e) {
                $errors[] = "{$registration->getRegistrationId()} => {$e->getMessage()}";
            }
            if ($didRollover) {
                $sent++;
            } else {
                $skipped++;
            }

            if ($sent % 10 == 0) {
                $output->write(['.']);
                // We need to throttle so we don't overload our limits with AWS
                // Current limit is 14 emails a second. So stopping at 10 just to make sure
                // Sleeping extra 4 seconds, because of issues when sleep was 1 second
                //sleep(5);
            }
        }

        $end = microtime(true);
        $totalTime = ($end - $start);

        $output->writeln([
            '',
            'Sent ' . $sent . ' emails',
            'Skipped (Inactive/Refunded/PickedUp) ' . $skipped,
            'Found ' . count($errors). ' errors:',
            'Runtime of ' . round($totalTime) . ' seconds.',
            '',
        ]);

        foreach ($errors as $error) {
            $output->writeln([
                $error,
            ]);
        }

        $output->writeln(['Completed run.','']);

    }

    /**
     * @param Registration $oldRegistration
     * @return bool
     * @throws Exception
     */
    public function rolloverRegistration(Registration $oldRegistration) {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $registrationType = $entityManager
            ->getRepository(RegistrationType::class)
            ->getRegistrationTypeFromType('Rollover');
        $registrationStatusNew = $entityManager
            ->getRepository(RegistrationStatus::class)
            ->getRegistrationStatusFromStatus('New');
        $registrationStatusRollover = $entityManager
            ->getRepository(RegistrationStatus::class)
            ->getRegistrationStatusFromStatus('RolledOver');
        $registrationStatus = $oldRegistration->getRegistrationStatus();

        if (!$registrationStatus->getActive()) {
            return false;
        }

        $nextYear = (int)$oldRegistration->getEvent()->getYear() + 1;
        $nextEvent = $entityManager->getRepository(Event::class)->getEventFromYear($nextYear);
        if (!$nextEvent) {
            throw new Exception('Next con year not defined!');
        }

        $registration = new Registration();
        $registration->setEvent($nextEvent);
        $registration->setRegistrationStatus($registrationStatusNew);
        $registration->setRegistrationType($registrationType);
        $registration->setFirstName($oldRegistration->getFirstName());
        $registration->setMiddleName($oldRegistration->getMiddleName());
        $registration->setLastName($oldRegistration->getLastName());
        $registration->setBadgename($oldRegistration->getBadgeName());
        $registration->setEmail($oldRegistration->getEmail());
        $registration->setBirthday($oldRegistration->getBirthday());
        $registration->setAddress($oldRegistration->getAddress());
        $registration->setAddress2($oldRegistration->getAddress2());
        $registration->setCity($oldRegistration->getCity());
        $registration->setState($oldRegistration->getState());
        $registration->setZip($oldRegistration->getZip());
        $registration->setPhone($oldRegistration->getPhone());
        $registration->setContactNewsletter($oldRegistration->getContactNewsletter());
        $registration->setContactVolunteer($oldRegistration->getContactVolunteer());
        $number = $entityManager->getRepository(Registration::class)->generateNumber($registration);
        $registration->setNumber($number);

        $entityManager->persist($registration);
        $entityManager->flush();

        $oldBadges = $oldRegistration->getBadges();
        foreach ($oldBadges as $oldBadge) {
            /** @var Badge $oldBadge */
            if ($oldBadge->getBadgeType()->getName() == 'Staff') {
                // We will not rollover a staff badge
                continue;
            }
            $badge = new Badge();
            $number = $entityManager->getRepository(Badge::class)->generateNumber();
            $badge->setNumber($number);
            $badge->setBadgetype($oldBadge->getBadgetype());
            $badge->setBadgestatus($oldBadge->getBadgestatus());
            $badge->setRegistration($registration);
            $entityManager->persist($badge);
        }

        $oldRegistrationShirts = $oldRegistration->getRegistrationShirts();
        foreach ($oldRegistrationShirts as $oldRegistrationShirt) {
            /** @var RegistrationShirt $oldRegistrationShirt */
            $registrationShirt = new RegistrationShirt();
            $registrationShirt->setRegistration($registration);
            $registrationShirt->setShirt($oldRegistrationShirt->getShirt());
            $entityManager->persist($registrationShirt);
        }

        $oldHistory = '';
        $groups = $oldRegistration->getGroups();
        foreach ($groups as $group) {
            /** @var Group $group */
            $oldHistory = "Group Removed: {$group->getName()}<br>";
            $registration->removeGroup($group);
        }
        $entityManager->flush();

        $this->getContainer()->get('util_email')->sendBulkRolloverEmailTwentyTwenty($registration);

        $registrationHistory = new History();
        $registrationHistory->setRegistration($registration);
        $url = $this->getContainer()->get('router')->generate('viewRegistration', ['registrationId' => $oldRegistration->getRegistrationId()]);
        $history = " Transferred From <a href='$url'>"
            . $oldRegistration->getEvent()->getYear() . '</a>. <br>';
        $registrationHistory->setChangetext($history . '<br>Registration created from 2020 Bulk Rolled-over');
        $entityManager->persist($registrationHistory);

        $oldRegistration->setTransferredTo($registration);
        $oldRegistration->setRegistrationstatus($registrationStatusRollover);
        $entityManager->persist($oldRegistration);
        $entityManager->flush();

        $registrationHistory = new History();
        $registrationHistory->setRegistration($oldRegistration);
        $url = $this->getContainer()->get('router')->generate('viewRegistration', ['registrationId' => $registration->getRegistrationId()]);
        $oldHistory .= " Transferred To <a href='$url'>"
            . $registration->getEvent()->getYear() . '</a>. <br>';
        $registrationHistory->setChangetext($oldHistory . '<br>Registration 2020 Bulk Rolled-over');
        $entityManager->persist($registrationHistory);
        $entityManager->flush();

        $params = ['registrationId' => $oldRegistration->getRegistrationId()];
        return true;
    }
}

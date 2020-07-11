<?php
/**
 * Copyright (c) 2020. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */
declare(strict_types=1);

namespace AppBundle\Command;


use AppBundle\Entity\Registration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmailConfirmation extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test-send-mail';
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
        $registrations = [
            $doctrine
            ->getRepository(Registration::class)
            ->find(56338)
        ];

        try {
            $email = $this->getContainer()->get('util_email');
        } catch (\Exception $e) {
            //
        }

        $output->writeln([
            '2020 Rollover Script',
            '============',
            'Found ' . count($registrations) . ' registrations',
            '',
            'Starting rollovers and confirmation email sending: ',
        ]);

        $sent = 0;
        $skipped = 0;
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
                sleep(5);
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
     */
    public function rolloverRegistration(Registration $oldRegistration) {
        return $this->getContainer()->get('util_email')->sendBulkRolloverEmailTwentyTwenty($oldRegistration);
    }
}

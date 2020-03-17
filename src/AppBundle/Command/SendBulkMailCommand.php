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
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendBulkMailCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:send-bulk-mail';
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
            ->setDescription('Sends a bulk mail to all active registrations.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Allows you to email everyone')


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
        $mail = $this->getContainer()->get('util_email');

        /** @var Registration[] $registrations */
        $registrations = $doctrine
            ->getRepository(Registration::class)
            ->findActiveRegistrations();


        $output->writeln([
            'Email Cancellation sender',
            '============',
            'Found ' . count($registrations) . ' registrations',
            '',
            'Starting email sending: ',
        ]);

        $sent = 0;
        $groups = 0;
        $missingEmail = 0;
        $errors = [];
        foreach ($registrations as $registration) {
            if (count($registration->getGroups()) > 0) {
                // We don't send to groups
                $groups++;
                continue;
            }

            if (!$registration->getEmail()) {
                // Don't send if they don't have an email
                $missingEmail++;
                continue;
            }

            // Only send to my regisration right now to test the run
            if ($registration->getRegistrationId() == 56338) {
                try {
                    $mail->sendCancellationEmail($registration);
                } catch (\Exception $e) {
                    $errors[] = "{$registration->getRegistrationId()} => {$e->getMessage()}";
                }
            }
            $sent++;

            if ($sent % 10 == 0) {
                $output->write(['.']);
                // We need to throttle so we don't overload our limits with AWS
                // Current limit is 14 emails a second. So stopping at 10 just to make sure
                sleep(1);
            }
        }

        $end = microtime(true);
        $totalTime = ($end - $start);

        $output->writeln([
            '',
            'Sent ' . $sent . ' emails',
            'Group Registrations Skipped ' . $groups,
            'Missing Emails Skipped ' . $missingEmail,
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
}

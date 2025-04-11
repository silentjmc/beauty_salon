<?php

namespace App\Command;

use App\Entity\BeautySalon;
use App\Entity\Income;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * Command to send reminder emails for missing income submissions
 * 
 * This command checks all salon managers and sends reminder emails to those
 * who have not submitted their income reports for the previous month.
 * It's designed to be run as a scheduled task/cron job at the beginning of each month.
 */
#[AsCommand(
    name: 'app:send-income-reminders',
    description: 'Sends a reminder email to salon managers who didn’t submit income for the previous month.',
)]
class SendIncomeReminderEmail extends Command
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private string $senderEmail;
    private Environment $twig;

        /**
     * Constructor for SendIncomeReminderEmail command
     * 
     * Initializes the command with necessary dependencies for database access,
     * email sending capabilities, and template rendering.
     * 
     * @param EntityManagerInterface $entityManager For database operations
     * @param MailerInterface $mailer For sending emails
     * @param Environment $twig For rendering email templates
     * @param string $senderEmail Email address used as the sender
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Environment $twig,
        string $senderEmail = 'no-reply@example.com' // Replace with your sender email
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->senderEmail = $senderEmail;
    }

    /**
     * Executes the command logic
     * 
     * This method is the entry point for the command execution. It:
     * 1. Determines the previous month to check for missing income submissions
     * 2. Retrieves all salons from the database
     * 3. Checks each salon for missing income submissions
     * 4. Sends reminder emails to managers with missing submissions
     * 
     * @param InputInterface $input Command input
     * @param OutputInterface $output Command output
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Sending income submission reminders');

        // Get current date and previous month
        $now = new \DateTimeImmutable();
        $previousMonth = $now->modify('-1 month');
        $monthIncome = $previousMonth->format('m');
        $yearIncome = $previousMonth->format('Y');
        $formattedMonth = $previousMonth->format('F Y');

        // Find all salon managers
        $salons = $this->entityManager->getRepository(BeautySalon::class)->findAll();

        $reminderCount = 0;
        $io->progressStart(count($salons));

        foreach ($salons as $salon) {
            $io->progressAdvance();
            
            // Check if salon has submitted income for previous month
            $incomeExists = $this->entityManager->getRepository(Income::class)->findOneBy([
                'beautySalon' => $salon,
                'monthIncome' => $monthIncome,
                'yearIncome' => $yearIncome
            ]);

            // If no income submission found, send reminder email
            if (!$incomeExists) {
                $manager = $salon->getManager();
                if ($manager) {
                    $this->sendReminderEmail($manager, $salon, $formattedMonth);
                    $reminderCount++;
                }
            }
        }

        // Finalize progress bar and show success message
        $io->progressFinish();
        $io->success("Sent $reminderCount reminders to salon managers.");

        return Command::SUCCESS;
    }

    /**
     * Sends a reminder email to a salon manager
     * 
     * This method generates and sends an email to remind a salon manager
     * to submit their income report for the specified month.
     * 
     * @param User $manager The salon manager receiving the reminder
     * @param BeautySalon $salon The salon with missing income submission
     * @param string $formattedMonth The month (formatted as "Month Year") for which income submission is required
     * @return void
     */
    private function sendReminderEmail(User $manager, BeautySalon $salon, string $formattedMonth): void
    {
        // Render email body using Twig template
        $emailBody = $this->twig->render('email/income_reminder.html.twig', [
            'managerName' => $manager->getManagerLastName(),
            'salonName' => $salon->getName(),
            'formattedMonth' => $formattedMonth
        ]);
        
        // Create and configure email
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($manager->getEmail())
            ->subject("Rappel - Saisie du chiffre d'affaires pour $formattedMonth")
            ->html($emailBody);

        $this->mailer->send($email);
    }
}
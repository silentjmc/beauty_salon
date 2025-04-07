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

            if (!$incomeExists) {
                $manager = $salon->getManager();
                if ($manager) {
                    $this->sendReminderEmail($manager, $salon, $formattedMonth);
                    $reminderCount++;
                }
            }
        }

        $io->progressFinish();
        $io->success("Sent $reminderCount reminders to salon managers.");

        return Command::SUCCESS;
    }

    private function sendReminderEmail(User $manager, BeautySalon $salon, string $formattedMonth): void
    {
        $emailBody = $this->twig->render('email/income_reminder.html.twig', [
            'managerName' => $manager->getManagerLastName(),
            'salonName' => $salon->getName(),
            'formattedMonth' => $formattedMonth
        ]);
        
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($manager->getEmail())
            ->subject("Rappel - Saisie du chiffre d'affaires pour $formattedMonth")
            ->html($emailBody);
            //->html($this->getEmailTemplate($manager->getManagerLastName(), $salon->getName(), $formattedMonth));

        $this->mailer->send($email);
    }
/*
    private function getEmailTemplate(string $managerName, string $salonName, string $formattedMonth): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4A90E2; color: white; padding: 10px 20px; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rappel de saisie</h1>
        </div>
        <div class="content">
            <p>Bonjour {$managerName},</p>
            
            <p>Nous vous rappelons que la saisie du chiffre d'affaires de votre salon <strong>{$salonName}</strong> 
            pour le mois de <strong>{$formattedMonth}</strong> n'a pas encore été effectuée.</p>
            
            <p>Pour maintenir des statistiques précises et à jour, merci de bien vouloir saisir 
            cette information dès que possible.</p>
            
            <p>Vous pouvez effectuer cette saisie en vous connectant à votre espace personnel.</p>
            
            <p>Cordialement,<br>
            L'équipe BeautyStatistics</p>
        </div>
        <div class="footer">
            <p>Ce message est envoyé automatiquement. Merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }*/
}
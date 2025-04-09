<?php

namespace App\DataFixtures;

use App\Entity\BeautySalon;
use App\Entity\Department;
use App\Entity\Income;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Region;
use App\Entity\Statistic;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }
    public function load(ObjectManager $objectManager): void
    {
        $regions = [
            'Auvergne-Rhône-Alpes',
            'Bourgogne-Franche-Comté',
            'Bretagne',
            'Centre-Val de Loire',
            'Corse',
            'Grand Est',
            'Guadeloupe',
            'Guyane',
            'Hauts-de-France',
            'Ile-de-France',
            'La Réunion',
            'Martinique',
            'Mayotte',
            'Normandie',
            'Nouvelle-Aquitaine',
            'Occitanie',
            'Pays de la Loire',
            'Provence-Alpes-Côte d\'Azur',
        ];
        $regionEntities = [];
        $createdRegions = [];
        foreach ($regions as $index => $regionName) {
                    $region = new Region();
                    $region->setName($regionName);
                    $objectManager->persist($region);
                    $regionEntities[$index + 1] = $region;
                    $createdRegions[] = $region;
                };

        $departments = [
            ['Ain',	'01', 1],
            ['Allier',	'03',	1],
            ['Ardèche',	'07',	1],
            ['Cantal',	15,	1],
            ['Drôme',	26,	1],
            ['Isère',	38,	1],
            ['Loire',	42,	1],
            ['Haute-Loire',	43,	1],
            ['Puy-de-Dôme',	63,	1],
            ['Rhône',	69,	1],
            ['Savoie',	73,	1],
            ['Haute-Savoie',	74,	1],
            ['Côte-d\'Or',	21,	2],
            ['Doubs',	25,	2],
            ['Jura',	39,	2],
            ['Nièvre',	58,	2],
            ['Haute-Saône',	70,	2],
            ['Saône-et-Loire',	71,	2],
            ['Yonne',	89,	2],
            ['Territoire-de-Belfort',	90,	2],
            ['Côtes d\'Armor',	22,	3],
            ['Finistère',	29,	3],
            ['Ille-et-Vilaine',	35,	3],
            ['Morbihan',	56,	3],
            ['Cher',	18,	4],
            ['Eure-et-Loir',	28,	4],
            ['Indre',	36,	4],
            ['Indre-et-Loire',	37,	4],
            ['Loir-et-Cher',	41,	4],
            ['Loiret',	45,	4],
            ['Corse-du-Sud',	'2A',	5],
            ['Haute-Corse',	'2B',	5],
            ['Ardennes',	'08',	6],
            ['Aube',	10,	6],
            ['Marne',	51,	6],
            ['Haute-Marne',	52,	6],
            ['Meurthe-et-Moselle',	54,	6],
            ['Meuse',	55,	6],
            ['Moselle',	57,	6],
            ['Bas-Rhin',	67,	6],
            ['Haut-Rhin',	68,	6],
            ['Vosges',	88,	6],
            ['Guadeloupe',	971,	7],
            ['Guyane',	973,	8],
            ['Aisne', '02',	9],
            ['Nord',	59,	9],
            ['Oise',	60,	9],
            ['Pas-de-Calais',	62,	9],
            ['Somme',	80,	9],
            ['Paris',	75,	10],
            ['Seine-et-Marne',	77,	10],
            ['Yvelines',	78,	10],
            ['Essonne',	91,	10],
            ['Hauts-de-Seine',	92,	10],
            ['Seine-Saint-Denis',	93,	10],
            ['Val-de-Marne',	94,	10],
            ['Val-D\'Oise',	95,	10],
            ['La Réunion',	974,	11],
            ['Martinique',	972,	12],
            ['Mayotte',	976,	13],
            ['Calvados',	14,	14],
            ['Eure',	27,	14],
            ['Manche',	50,	14],
            ['Orne',	61,	14],
            ['Seine-Maritime',	76,	14],
            ['Charente',	16,	15],
            ['Charente-Maritime',	17,	15],
            ['Corrèze',	19,	15],
            ['Creuse',	23,	15],
            ['Dordogne',	24,	15],
            ['Gironde',	33,	15],
            ['Landes',	40,	15],
            ['Lot-et-Garonne',	47,	15],
            ['Pyrénées-Atlantiques',	64,	15],
            ['Deux-Sèvres',	79,	15],
            ['Vienne',	86,	15],
            ['Haute-Vienne',	87,	15],
            ['Ariège',	'09',	16],
            ['Aude',	11,	16],
            ['Aveyron',	12,	16],
            ['Gard',	30,	16],
            ['Haute-Garonne',	31,	16],
            ['Gers',	32,	16],
            ['Hérault',	34,	16],
            ['Lot',	46,	16],
            ['Lozère',	48,	16],
            ['Hautes-Pyrénées',	65,	16],
            ['Pyrénées-Orientales',	66,	16],
            ['Tarn',	81,	16],
            ['Tarn-et-Garonne',	82,	16],
            ['Loire-Atlantique',	44,	17],
            ['Maine-et-Loire',	49,	17],
            ['Mayenne',	53,	17],
            ['Sarthe',	72,	17],
            ['Vendée',	85,	17],
            ['Alpes-de-Haute-Provence',	'04',	18],
            ['Hautes-Alpes',	'05',	18],
            ['Alpes-Maritimes',	'06',	18],
            ['Bouches-du-Rhône',	13,	18],
            ['Var',	83,	18],
            ['Vaucluse',	84,	18],
        ];
        $createdDepartments = [];
        foreach ($departments as [$name,$code,$regionId]) {
            $department = new Department();
            $department->setName($name);
            $department->setCode($code);
            $department->setRegion($regionEntities[$regionId]);
            $objectManager->persist($department);
            $createdDepartments[] = $department;
        };

        $users = [
            ['mdubois@example.com', 'marie!D1', ['ROLE_USER'], 'Marie', 'Dubois'],
            ['jmartin@example.com', 'jean!M02', ['ROLE_USER'], 'Jean', 'Martin'],
            ['sbernard@example.com', 'sophie!B3', ['ROLE_USER'], 'Sophie', 'Bernard'],
            ['pmoreau@example.com', 'pierre!M4', ['ROLE_USER'], 'Pierre', 'Moreau'],
            ['clefevre@example.com', 'claire!L5', ['ROLE_USER'], 'Claire', 'Lefèvre'],      
        ];

        $createdUsers = [];
        foreach ($users as [$email, $password, $roles, $firstName, $lastName]) {
            $user = new User();
            $user->setEmail($email);
            $user->setManagerFirstName($firstName);
            $user->setManagerLastName($lastName);
            $user->setRoles($roles);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
            $objectManager->persist($user);
            $createdUsers[] = $user;
        }

        $beautySalons = [
            ['L\'Éclat des Sens', '12 Rue des Lilas', '75020', 'Paris', new \DateTime('2020-01-01'), 10, $createdUsers[0], $createdDepartments[50]],
            ['Evasion Epidermique','34 Rue du Commerce', '69003', 'Lyon', new \DateTime('2020-01-01'), 10, $createdUsers[1], $createdDepartments[9]], 
            ['Soleil de L\'Est', '8 Place des Halles', '13001', 'Marseille', new \DateTime('2020-01-01'), 10, $createdUsers[2], $createdDepartments[98]],
            ['La Belle Etoile', '27 Rue de la Paix', '33000', 'Bordeaux', new \DateTime('2020-01-01'), 10, $createdUsers[3], $createdDepartments[70]],
            ['Eclat de Peau', '15 Rue Montmartre', '06000', 'Nice', new \DateTime('2020-01-01'), 10, $createdUsers[4], $createdDepartments[97]],
        ];
        $createdBeautySalons = [];
        foreach ($beautySalons as [$name, $street, $zipCode, $city, $openingDate, $numberEmployeeFulltime, $manager, $department]) {
            $beautySalon = new BeautySalon();
            $beautySalon->setName($name);
            $beautySalon->setStreet($street);
            $beautySalon->setZipCode($zipCode);
            $beautySalon->setCity($city);
            $beautySalon->setOpeningDate($openingDate);
            $beautySalon->setNumberEmployeeFulltime($numberEmployeeFulltime);
            $beautySalon->setManager($manager);
            $beautySalon->setDepartment($department);
            $objectManager->persist($beautySalon);
            $createdBeautySalons[] = $beautySalon;
        }

        $incomes = [
            [$createdBeautySalons[0],"10000", new \DateTimeImmutable("2025-03-01"), 2, 2025],
            [$createdBeautySalons[1],"12000", new \DateTimeImmutable("2025-03-01"), 2, 2025],
            [$createdBeautySalons[2],"15000", new \DateTimeImmutable("2025-03-01"), 2, 2025],
            [$createdBeautySalons[3],"18000", new \DateTimeImmutable("2025-03-01"), 2, 2025],
            [$createdBeautySalons[4],"20000", new \DateTimeImmutable("2025-03-01"), 2, 2025],
            [$createdBeautySalons[0],"13000", new \DateTimeImmutable("2025-02-01"), 1, 2025],
            [$createdBeautySalons[1],"16000", new \DateTimeImmutable("2025-02-01"), 1, 2025],
            [$createdBeautySalons[2],"19000", new \DateTimeImmutable("2025-02-01"), 1, 2025],
            [$createdBeautySalons[3],"21000", new \DateTimeImmutable("2025-02-01"), 1, 2025],
            [$createdBeautySalons[4],"11000", new \DateTimeImmutable("2025-02-01"), 1, 2025]
        ];
        foreach ($incomes as [$beautySalon, $incomeAmount, $dateIncome, $month, $year]) {
            if (!$beautySalon instanceof BeautySalon) {
                throw new \InvalidArgumentException('beautySalon must be an instance of BeautySalon');
            }
            $income = new Income();
            $income->setBeautysalon($beautySalon);
            $income->setIncome($incomeAmount);
            $income->setCreatedAt($dateIncome);
            $income->setMonthIncome($month);
            $income->setYearIncome($year);
            $objectManager->persist($income);
        };

        $statistics = [
            [null, null, "France", 2025, 1, 15000],
            [null, null, "France", 2025, 2, 16000],
            [$createdDepartments[50],null, 'Departement', 2025, 1, 10000 ],
            [$createdDepartments[50],null, 'Departement', 2025, 2, 13000 ],
            [$createdDepartments[9],null, 'Departement', 2025, 1, 15000 ],
            [$createdDepartments[9],null, 'Departement', 2025, 2, 16000 ],
            [$createdDepartments[98],null, 'Departement', 2025, 1, 15000 ],
            [$createdDepartments[98],null, 'Departement', 2025, 2, 19000 ],
            [$createdDepartments[70],null, 'Departement', 2025, 1, 18000 ],
            [$createdDepartments[70],null, 'Departement', 2025, 2, 21000 ],
            [$createdDepartments[97],null, 'Departement', 2025, 1, 20000 ],
            [$createdDepartments[97],null, 'Departement', 2025, 2, 11000 ],
            [null, $createdRegions[9], 'Region', 2025, 1, 10000 ],
            [null, $createdRegions[9], 'Region', 2025, 2, 13000 ],
            [null, $createdRegions[0], 'Region', 2025, 1, 12000 ],
            [null, $createdRegions[0], 'Region', 2025, 2, 16000 ],
            [null, $createdRegions[17], 'Region', 2025, 1, 17500 ],
            [null, $createdRegions[17], 'Region', 2025, 2, 15000 ],
            [null, $createdRegions[14], 'Region', 2025, 1, 18000 ],
            [null, $createdRegions[14], 'Region', 2025, 2, 21000 ]
        ];
        $uniqueStatistics = [];
        foreach ($statistics as [$department, $region, $area, $year, $month, $income]) {
            $key = serialize([$department, $region, $area, $year, $month]);
            if (!isset($uniqueStatistics[$key])) {
                $uniqueStatistics[$key] = [$department, $region, $area, $year, $month, $income];
            }
        }
        foreach ($uniqueStatistics as $statistic) {
            $statisticEntity = new Statistic();
            $statisticEntity->setDepartment($statistic[0]);
            $statisticEntity->setRegion($statistic[1]);
            $statisticEntity->setArea($statistic[2]);
            $statisticEntity->setYear($statistic[3]);
            $statisticEntity->setMonth($statistic[4]);
            $statisticEntity->setAverageIncome($statistic[5]);
            $objectManager->persist($statisticEntity);
        }
        $objectManager->flush();
    }
}

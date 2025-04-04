<?php

namespace App\DataFixtures;

use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Region;
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
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
        foreach ($regions as $index => $regionName) {
                    $region = new Region();
                    $region->setName($regionName);
                    $manager->persist($region);
                    $regionEntities[$index + 1] = $region;
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

        foreach ($departments as [$name,$code,$regionId]) {
            $department = new Department();
            $department->setName($name);
            $department->setCode($code);
            $department->setRegion($regionEntities[$regionId]);
            $manager->persist($department);
        };

        $manager->flush();
        // $product = new Product();
        // $manager->persist($product);


    }
}

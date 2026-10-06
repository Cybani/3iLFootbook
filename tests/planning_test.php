<?php
// Exécution : C:\xampp\php\php.exe tests\planning_test.php
// Ces tests vérifient les créneaux sans connexion à PostgreSQL.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../php/planning.php';

$testsReussis = 0;
$testsEchoues = 0;

function verifierPlanningTest(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function comparerPlanningTest($attendu, $obtenu, string $message): void
{
    verifierPlanningTest($attendu === $obtenu, $message . "\nAttendu : " . var_export($attendu, true)
        . "\nObtenu : " . var_export($obtenu, true));
}

function testerPlanning(string $nom, callable $test): void
{
    global $testsReussis, $testsEchoues;
    try {
        $test();
        $testsReussis++;
        echo "OK - $nom\n";
    } catch (Throwable $erreur) {
        $testsEchoues++;
        fwrite(STDERR, "ECHEC - $nom : " . $erreur->getMessage() . "\n");
    }
}

function reservationPlanningTest(string $debut, string $fin): array
{
    return ['heure_debut' => $debut, 'heure_fin' => $fin];
}

function creneauPlanningTest(string $debut, string $fin, bool $occupe): array
{
    return ['debut' => $debut, 'fin' => $fin, 'occupe' => $occupe];
}

function tempsPlanningTest(string $heure): int
{
    $parties = array_map('intval', explode(':', $heure));
    return $parties[0] * 3600 + $parties[1] * 60 + ($parties[2] ?? 0);
}

// Une journée est entièrement couverte par des intervalles de durée positive,
// avec des états alternés. Chaque état doit correspondre aux réservations.
function verifierCouverturePlanningTest(array $reservations, string $ouverture = '08:00', string $fermeture = '22:00'): void
{
    $creneaux = creneauxPlanning($reservations, $ouverture, $fermeture);
    verifierPlanningTest(count($creneaux) > 0, 'Une journée doit contenir au moins un créneau.');
    $curseur = tempsPlanningTest($ouverture);
    $finJour = tempsPlanningTest($fermeture);
    $etatPrecedent = null;

    foreach ($creneaux as $creneau) {
        $debut = tempsPlanningTest($creneau['debut']);
        $fin = tempsPlanningTest($creneau['fin']);
        comparerPlanningTest($curseur, $debut, 'Trou ou chevauchement entre les créneaux.');
        verifierPlanningTest($fin > $debut && $fin <= $finJour, 'Durée ou fin de créneau invalide.');
        verifierPlanningTest(is_bool($creneau['occupe']), 'Le statut doit être booléen.');
        verifierPlanningTest($etatPrecedent === null || $etatPrecedent !== $creneau['occupe'], 'Deux créneaux successifs ont le même statut.');

        // Les bornes sont aussi sondées : une séparation incorrecte au milieu
        // d'une réservation ne peut pas passer ce contrôle.
        foreach ([$debut, ($debut + $fin) / 2, $fin - 0.5] as $instant) {
            $occupeAttendu = false;
            foreach ($reservations as $reservation) {
                $debutReservation = tempsPlanningTest($reservation['heure_debut']);
                $finReservation = tempsPlanningTest($reservation['heure_fin']);
                if ($instant >= $debutReservation && $instant < $finReservation) {
                    $occupeAttendu = true;
                    break;
                }
            }
            comparerPlanningTest($occupeAttendu, $creneau['occupe'], 'Le statut ne correspond pas aux réservations.');
        }
        $curseur = $fin;
        $etatPrecedent = $creneau['occupe'];
    }
    comparerPlanningTest($finJour, $curseur, 'La journée ne se termine pas à la fermeture.');
}

testerPlanning('Dates invalides et années bissextiles', function (): void {
    foreach (['', 'invalide', '2026-02-29', '2026-04-31', '2026-13-01', '2026-2-01', '2026-10-04 suite', '0000-01-01', '2026-10-04' . chr(0)] as $valeur) {
        comparerPlanningTest(null, datePlanningValide($valeur), 'Date invalide acceptée : ' . $valeur);
    }
    $date = datePlanningValide('2024-02-29');
    verifierPlanningTest($date instanceof DateTimeImmutable, 'Le 29 février 2024 doit être accepté.');
    comparerPlanningTest('2024-02-29 00:00:00', $date->format('Y-m-d H:i:s'), 'Date bissextile incorrecte.');
    comparerPlanningTest('Europe/Paris', $date->getTimezone()->getName(), 'Fuseau de la date incorrect.');
    comparerPlanningTest(null, datePlanningValide('2100-02-29'), '2100 ne doit pas être considéré bissextile.');
});

testerPlanning('Lundi, dimanche et semaine à cheval sur deux années', function (): void {
    foreach (['2026-09-28' => '2026-09-28', '2026-10-04' => '2026-09-28', '2027-01-01' => '2026-12-28', '2027-01-03' => '2026-12-28'] as $jour => $lundi) {
        $debut = debutSemainePlanning(datePlanningValide($jour)->setTime(18, 45));
        comparerPlanningTest($lundi . ' 00:00:00', $debut->format('Y-m-d H:i:s'), 'Mauvais début de semaine pour ' . $jour);
    }
    $semaine = debutSemainePlanning(datePlanningValide('2027-01-01'));
    comparerPlanningTest('2027-01-03', $semaine->modify('+6 days')->format('Y-m-d'), 'Fin de semaine incorrecte au changement d’année.');
});

testerPlanning('Journée vide disponible de 08:00 à 22:00', function (): void {
    comparerPlanningTest([creneauPlanningTest('08:00', '22:00', false)], creneauxPlanning([]), 'Journée vide incorrecte.');
});

testerPlanning('Réservation exacte 09:30 à 10:15 et plages libres autour', function (): void {
    $reservations = [reservationPlanningTest('09:30', '10:15')];
    comparerPlanningTest([
        creneauPlanningTest('08:00', '09:30', false),
        creneauPlanningTest('09:30', '10:15', true),
        creneauPlanningTest('10:15', '22:00', false),
    ], creneauxPlanning($reservations), 'Les minutes de la réservation doivent être conservées.');
});

testerPlanning('Fusion des occupations adjacentes, chevauchantes et imbriquées', function (): void {
    $reservations = [
        reservationPlanningTest('11:00', '12:00'),
        reservationPlanningTest('09:00', '10:00'),
        reservationPlanningTest('09:30', '10:30'),
        reservationPlanningTest('10:30', '11:00'),
        reservationPlanningTest('09:45', '10:00'),
    ];
    comparerPlanningTest([
        creneauPlanningTest('08:00', '09:00', false),
        creneauPlanningTest('09:00', '12:00', true),
        creneauPlanningTest('12:00', '22:00', false),
    ], creneauxPlanning($reservations), 'Une occupation continue ne doit créer aucun faux créneau libre.');
});

testerPlanning('Bornes de la journée et occupations hors des horaires', function (): void {
    $reservations = [
        reservationPlanningTest('06:00', '08:00'),
        reservationPlanningTest('07:30', '09:00'),
        reservationPlanningTest('21:00', '23:30'),
        reservationPlanningTest('22:00', '23:00'),
    ];
    comparerPlanningTest([
        creneauPlanningTest('08:00', '09:00', true),
        creneauPlanningTest('09:00', '21:00', false),
        creneauPlanningTest('21:00', '22:00', true),
    ], creneauxPlanning($reservations), 'Les réservations doivent être limitées aux heures d’ouverture.');
});

testerPlanning('Journée entièrement occupée', function (): void {
    foreach ([
        [reservationPlanningTest('08:00', '22:00')],
        [reservationPlanningTest('06:00', '23:00')],
        [reservationPlanningTest('08:00', '14:00'), reservationPlanningTest('14:00', '22:00')],
    ] as $reservations) {
        comparerPlanningTest([creneauPlanningTest('08:00', '22:00', true)], creneauxPlanning($reservations), 'Une journée pleine ne doit produire aucun créneau libre.');
    }
});

testerPlanning('Conservation des secondes non nulles', function (): void {
    comparerPlanningTest([
        creneauPlanningTest('08:00', '09:30:15', false),
        creneauPlanningTest('09:30:15', '10:15:45', true),
        creneauPlanningTest('10:15:45', '22:00', false),
    ], creneauxPlanning([reservationPlanningTest('09:30:15', '10:15:45')]), 'Les secondes ne doivent pas arrondir les disponibilités.');
    comparerPlanningTest(34215, secondesPlanning('09:30:15'), 'Conversion des secondes incorrecte.');
    comparerPlanningTest('09:30:15', heurePlanning(34215), 'Formatage des secondes incorrect.');
});

testerPlanning('Horaires invalides et réservations incohérentes', function (): void {
    foreach ([['22:00', '08:00'], ['08:00', '08:00'], ['invalide', '22:00'], ['08:00', '24:00']] as [$ouverture, $fermeture]) {
        $exceptionLevee = false;
        try {
            creneauxPlanning([], $ouverture, $fermeture);
        } catch (InvalidArgumentException $erreur) {
            $exceptionLevee = true;
        }
        verifierPlanningTest($exceptionLevee, 'Les horaires invalides doivent être refusés.');
    }
    comparerPlanningTest([creneauPlanningTest('08:00', '22:00', false)], creneauxPlanning([
        reservationPlanningTest('10:00', '10:00'),
        reservationPlanningTest('11:00', '09:00'),
        reservationPlanningTest('invalide', '12:00'),
    ]), 'Les réservations invalides doivent être ignorées.');
});

testerPlanning('Couverture sans trous et statuts corrects sur plusieurs journées', function (): void {
    $jeux = [
        [],
        [reservationPlanningTest('09:30', '10:15')],
        [reservationPlanningTest('07:00', '09:00'), reservationPlanningTest('21:00', '23:00')],
        [reservationPlanningTest('08:00', '22:00')],
        [reservationPlanningTest('09:30:15', '10:15:45')],
        [reservationPlanningTest('12:00', '16:00'), reservationPlanningTest('08:00', '12:00'), reservationPlanningTest('10:00', '14:00')],
        [reservationPlanningTest('06:00', '07:00'), reservationPlanningTest('22:00', '23:30')],
        [reservationPlanningTest('08:00', '08:00:01'), reservationPlanningTest('08:00:02', '08:00:03'), reservationPlanningTest('21:59:59', '22:00')],
    ];
    foreach ($jeux as $reservations) {
        verifierCouverturePlanningTest($reservations);
        verifierCouverturePlanningTest(array_reverse($reservations));
    }
    verifierCouverturePlanningTest([
        reservationPlanningTest('09:00', '10:00'),
        reservationPlanningTest('16:00', '18:00'),
    ], '09:15:30', '17:15:15');
});

echo "$testsReussis tests réussis, $testsEchoues tests échoués.\n";
exit($testsEchoues > 0 ? 1 : 0);

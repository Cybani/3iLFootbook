<?php
// Fonctions du planning, séparées de l'affichage pour pouvoir les vérifier.

function datePlanningValide(string $valeur): ?DateTimeImmutable
{
    if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $valeur, $parties)
        || !checkdate((int)$parties[2], (int)$parties[3], (int)$parties[1])) {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $valeur, new DateTimeZone('Europe/Paris'));
    return $date && $date->format('Y-m-d') === $valeur ? $date : null;
}

function debutSemainePlanning(DateTimeImmutable $date): DateTimeImmutable
{
    // N vaut 1 le lundi et 7 le dimanche.
    return $date->setTime(0, 0)->modify('-' . ((int)$date->format('N') - 1) . ' days');
}

function secondesPlanning(string $heure): ?int
{
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', $heure, $parties)) {
        return null;
    }
    return (int)$parties[1] * 3600 + (int)$parties[2] * 60 + (int)($parties[3] ?? 0);
}

function heurePlanning(int $secondes): string
{
    $heure = sprintf('%02d:%02d', intdiv($secondes, 3600), intdiv($secondes % 3600, 60));
    return $secondes % 60 === 0 ? $heure : $heure . sprintf(':%02d', $secondes % 60);
}

function creneauxPlanning(array $reservations, string $ouverture = '08:00', string $fermeture = '22:00'): array
{
    $debutJour = secondesPlanning($ouverture);
    $finJour = secondesPlanning($fermeture);
    if ($debutJour === null || $finJour === null || $debutJour >= $finJour) {
        throw new InvalidArgumentException("Horaires d'ouverture invalides.");
    }

    $occupations = [];
    foreach ($reservations as $reservation) {
        $debut = secondesPlanning((string)$reservation['heure_debut']);
        $fin = secondesPlanning((string)$reservation['heure_fin']);
        if ($debut === null || $fin === null || $fin <= $debut) {
            continue;
        }
        $debut = max($debutJour, $debut);
        $fin = min($finJour, $fin);
        if ($debut < $fin) {
            $occupations[] = ['debut' => $debut, 'fin' => $fin];
        }
    }
    usort($occupations, fn(array $a, array $b): int => $a['debut'] <=> $b['debut']);

    // Fusionner les occupations adjacentes ou chevauchantes évite les faux trous.
    $occupationsFusionnees = [];
    foreach ($occupations as $occupation) {
        $dernier = count($occupationsFusionnees) - 1;
        if ($dernier >= 0 && $occupation['debut'] <= $occupationsFusionnees[$dernier]['fin']) {
            $occupationsFusionnees[$dernier]['fin'] = max($occupationsFusionnees[$dernier]['fin'], $occupation['fin']);
        } else {
            $occupationsFusionnees[] = $occupation;
        }
    }

    $creneaux = [];
    $curseur = $debutJour;
    foreach ($occupationsFusionnees as $occupation) {
        if ($curseur < $occupation['debut']) {
            $creneaux[] = ['debut' => heurePlanning($curseur), 'fin' => heurePlanning($occupation['debut']), 'occupe' => false];
        }
        $creneaux[] = ['debut' => heurePlanning($occupation['debut']), 'fin' => heurePlanning($occupation['fin']), 'occupe' => true];
        $curseur = $occupation['fin'];
    }
    if ($curseur < $finJour) {
        $creneaux[] = ['debut' => heurePlanning($curseur), 'fin' => heurePlanning($finJour), 'occupe' => false];
    }
    return $creneaux;
}

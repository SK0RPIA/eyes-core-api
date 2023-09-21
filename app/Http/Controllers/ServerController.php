<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServerController extends Controller
{

    public function initPanel()
    {
        $this->setHeader();
        $output = shell_exec("cat /proc/cpuinfo | grep 'model name' | head -n 1 | awk -F ': ' '{print $2}'");
        $output2 = shell_exec("nproc --all");
        $cpuName = trim($output);
        $totalCore = floatval($output2);


        $jsonData = ['total_core' => $totalCore, 'cpu_name' => $cpuName,];


        return response()->json($jsonData);

    }

    public function getCpuPercentage()
    {
        $this->setHeader();
        $output = shell_exec("top -bn 1 | awk '/Cpu\(s\):/ {print $2}'");
        $totalUsage = floatval($output);

        // Conversion du pourcentage total d'utilisation en une chaîne JSON
        $jsonData = ['total_usage' => $totalUsage];


        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($jsonData);
    }

    public function getRamData()
    {
        $this->setHeader();

        $output = shell_exec('free');
        $lines = explode("\n", $output);

        $memoryValues = preg_split('/\s+/', $lines[1]);
        $jsonData = [
            'total' => $memoryValues[1],
            'utilized' => $memoryValues[2],
            'cache' => $memoryValues[5],
        ];



        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($jsonData);
    }

    public function getRaidStatus()
    {
        $this->setHeader();

        // Exécution de la commande 'mdadm' pour obtenir le statut des raids
        $output = shell_exec('mdadm --detail /dev/md0'); // Remplacez /dev/md0 par le nom correct de votre RAID

        // Vérification si la sortie est vide, ce qui signifie que le RAID n'est pas configuré
        if (empty(trim($output))) {
            return response()->json(['error' => 'No RAID configuration found'], 404);
        }

        // Extraction des informations pertinentes de la sortie
        $lines = explode("\n", $output);
        $raidData = [];
        foreach ($lines as $line) {
            if (preg_match('/^State : (.+)/', $line, $matches)) {
                $raidData['state'] = trim($matches[1]);
            }
            if (preg_match('/^Raid Level : (.+)/', $line, $matches)) {
                $raidData['level'] = trim($matches[1]);
            }
            // ... (ajoutez d'autres expressions régulières pour extraire plus d'informations)
        }

        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($raidData);
    }

    public function getDiskData()
    {
        $this->setHeader();

        // Exécution de la commande 'lsblk' pour obtenir la liste des disques et partitions
        $lsblkOutput = shell_exec('lsblk --output NAME,TYPE,MOUNTPOINT');
        $lines = explode("\n", trim($lsblkOutput));

        $diskData = [];
        $partitions = []; // Tableau pour stocker les partitions

        foreach ($lines as $line) {
            if (preg_match('/^(\S+)\s+(\S+)\s*(\S*)/', $line, $matches)) {
                $cleanedName = preg_replace('/[^a-zA-Z0-9]/', '', $matches[1]); // Nettoyer le nom

                $diskInfo = [
                    'name' => $cleanedName,
                    'type' => $matches[2],
                    'mount_point' => $matches[3] ?? null,
                ];

                if ($diskInfo['name'] !== "NAME") {
                    $dfOutput = shell_exec('df /dev/' . $diskInfo['name']);

                    $dfLines = explode("\n", trim($dfOutput));

                    if (count($dfLines) > 1) {
                        $diskLine = $dfLines[1]; // Prendre la seconde ligne
                        if (preg_match('/\/dev\/\S+\s+(\d+)\s+(\d+)\s+\d+\s+(\d+)%/', $diskLine, $dfMatches)) {
                            $diskInfo['storage_total'] = intval($dfMatches[1]) * 1024; // Convertir les blocs de 1K en octets
                            $diskInfo['storage_used'] = intval($dfMatches[2]) * 1024; // Convertir les blocs de 1K en octets
                            $diskInfo['storage_usage_percentage'] = $dfMatches[3] . '%';
                        }
                    }

                    // Si c'est un disque, ajoutez-le à la liste principale
                    if ($diskInfo['type'] === 'disk') {
                        $diskInfo['partitions'] = []; // Initialiser les partitions
                        $diskData[$diskInfo['name']] = $diskInfo;
                    }
                    // Si c'est une partition, trouvez le disque parent et ajoutez-la à ses partitions
                    elseif (isset($diskData[substr($diskInfo['name'], 0, 3)])) {
                        $diskData[substr($diskInfo['name'], 0, 3)]['partitions'][] = $diskInfo;
                        $partitions[] = $diskInfo; // Ajouter la partition à la liste des partitions
                    }
                }
            }
        }

        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($partitions); // Renvoyer uniquement le tableau des partitions
    }







}
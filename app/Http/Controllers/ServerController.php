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

        // Exécution de la commande 'lsblk' pour obtenir la liste des disques
        $output = shell_exec('lsblk --output NAME,SIZE,TYPE,MOUNTPOINT');
        $lines = explode("\n", trim($output));

        $diskData = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s*(\S*)/', $line, $matches)) {
                $diskInfo = [
                    'name' => $matches[1],
                    'size' => $matches[2],
                    'type' => $matches[3],
                    'mount_point' => $matches[4] ?? null,
                ];

                // Si le type est 'disk', alors nous procédons à obtenir plus de détails
                if ($diskInfo['type'] == 'disk') {
                    // Exécution de la commande 'df' pour obtenir le pourcentage d'utilisation du stockage et l'espace utilisé
                    $dfOutput = shell_exec('df -h /dev/' . $diskInfo['name']);
                    if (preg_match('/(\d+)\%\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)/', $dfOutput, $dfMatches)) {
                        $diskInfo['storage_usage_percentage'] = $dfMatches[1] . '%';
                        $diskInfo['storage_used'] = $dfMatches[3];
                        $diskInfo['storage_available'] = $dfMatches[4];
                    }
                }



                if ($diskInfo['name'] != "NAME")
                    array_push($diskData, $diskInfo);
            }
        }

        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($diskData);
    }



}
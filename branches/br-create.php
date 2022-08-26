<?php

$branch  = isset($argv[1]) ? $argv[1] : null;
$project = isset($argv[2]) ? $argv[2] : 'arm';
if ($branch) {
    include 'repositories.php';
    $repo = getRepositoryPath($project);
    if ($repo) {
        $domain0                = 'acr';
        $branchesDir            = '/home/acround/workspace/branches';
        $branchDir              = $branchesDir . DIRECTORY_SEPARATOR . $branch;
        $repoBranchDir          = $repo . DIRECTORY_SEPARATOR . 'branches' . DIRECTORY_SEPARATOR . $branch;
        $apacheDir              = '/etc/apache2';
        $domainName             = $branch . '.' . $project . '.' . $domain0;
        $apacheConfFile         = array(
            '<VirtualHost *:80>',
            '	AddDefaultCharset WINDOWS-1251',
            '	DocumentRoot "' . $branchDir . '"',
            '	ServerName ' . $domainName,
            '	ErrorLog "/var/log/apache2/' . $domainName . '.log"',
            '	CustomLog "/var/log/apache2/' . $domainName . '.common.log" common',
            '</VirtualHost>',
        );
        $apacheConfFileName     = $apacheDir . DIRECTORY_SEPARATOR . 'sites-available' . DIRECTORY_SEPARATOR . $branch . '.conf';
        file_put_contents($apacheConfFileName, implode("\n", $apacheConfFile));
        $apacheConfFileLinkName = $apacheDir . DIRECTORY_SEPARATOR . 'sites-enabled' . DIRECTORY_SEPARATOR . $branch . '.conf';
        symlink($apacheConfFileName, $apacheConfFileLinkName);
        if (file_exists($apacheConfFileName)) {
            mkdir($branchDir);
            chdir($branchDir);
            exec('svn cp ' . $repo . DIRECTORY_SEPARATOR . 'trunk' . ' ' . $repoBranchDir . ' -m \'\'');
            exec('svn co ' . $repoBranchDir . ' ./');
            exec('svn info', $out);
            echo implode("\n", $out);
            exec('/etc/init.d/apache2 restart', $out);
            echo implode("\n", $out);
        } else {
            echo 'Root access!!!';
        }
    } else {
        echo "No repository\n";
    }
} else {
    echo "No branch name\n";
}

<?php

function getRepositoryPath($name)
{
    $repositories = array(
        'arm' => 'file:///home/acround/workspace/noss-svn/noss-test/arm',
    );
    if (isset($repositories[$name])) {
        return $repositories[$name];
    } else {
        return null;
    }
}

<?php

$saveDir = './tests/acceptance/tmp';
$stubDir = './tests/acceptance/stubs';
$commandToGenerate = 'FooCommand';

$I = new AcceptanceTester($scenario);
$I->wantTo('generate a command and handler class');

$I->runShellCommand("php ../../../artisan commander:generate $commandToGenerate --properties='bar, baz' --base='$saveDir'");
$I->seeInShellOutput('All done!');

// My Command stub should match the generated class.
$I->openFile("{$saveDir}/{$commandToGenerate}.php");
$I->seeFileContentsEqual(file_get_contents("{$stubDir}/{$commandToGenerate}.stub"));

// And my CommandHandler stub should match its generated counterpart, as well.
$I->openFile("{$saveDir}/{$commandToGenerate}Handler.php");
$I->seeFileContentsEqual(file_get_contents("{$stubDir}/{$commandToGenerate}Handler.stub"));

$I->cleanDir($saveDir);



<?php

class ExamplePharExploitClass { }
$dummy = new ExamplePharExploitClass();
$dummy->value = "EXPLOITED_VALUE";

@unlink("example0.phar");
@unlink("example1.phar");
@unlink("example2.phar");
@unlink("example3.phar");

$poc = new Phar("example0.phar");
$poc->startBuffering();
$poc->setStub("<?php __HALT_COMPILER();");
$poc["test_file.txt"] = "Bogus text";
$poc["subdir/test_file.txt"] = "Bogus text";
$poc->setMetadata($dummy);
$poc->stopBuffering();

// need multiple phars so we can test multiple different tries in our unit tests
// (without the copies we'd need to run tests in process isolation so each test would load it)
copy("example0.phar", "example1.phar");
copy("example0.phar", "example2.phar");
copy("example0.phar", "example3.phar");
copy("example0.phar", "example4.phar");

<?php
declare(strict_types=1);

#if (${NAMESPACE})
namespace ${NAMESPACE};
#end

use ${TESTED_NAMESPACE}\\${TESTED_NAME};
use PHPUnit\Framework\TestCase;

#parse("PHPUnit Class Doc Comment.php")
class ${NAME} extends TestCase
{
}

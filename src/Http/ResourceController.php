<?php

namespace JMWD\JsonApi\Http;

use JMWD\JsonApi\Http\Concerns;

class ResourceController extends BaseController
{
    use Concerns\CanList;
    use Concerns\CanRead;
    use Concerns\CanCreate;
}
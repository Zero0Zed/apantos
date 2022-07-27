<?php

namespace System\Database\ORM;

use System\Database\Traits\HasAttributes;
use System\Database\Traits\HasCRUD;
use System\Database\Traits\HasMethodCaller;
use System\Database\Traits\HasQueryBuilder;
use System\Database\Traits\HasRelation;

abstract class Model
{
    use HasCRUD;
    use HasAttributes;
    use HasMethodCaller;
    use HasQueryBuilder;
    use HasRelation;

    protected $table;

    protected $fillable = [''];

    protected $hidden = [];

    protected $casts = [];

    protected $primaryKey = 'id';

    protected $createdAt = 'created_at';

    protected $updatedAt = 'updated_at';

    protected $deletedAt = 'deleted_at';

    protected $collection = [];

    protected $is_relation = false;
}

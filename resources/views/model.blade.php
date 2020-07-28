@php echo "<?php"
@endphp


namespace {{ $modelNameSpace }};
@php
    $hasRoles = false;
    if(count($relations) && isset($relations["belongsToMany"]) && count($relations['belongsToMany'])) {
        $hasRoles = $relations['belongsToMany']->filter(function($belongsToMany) {
            return $belongsToMany['related_table'] == 'roles';
        })->count() > 0;
        $relations['belongsToMany'] = $relations['belongsToMany']->reject(function($belongsToMany) {
            return $belongsToMany['related_table'] == 'roles';
        });
    }
@endphp
/* Imports */
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
@if($fillable)@foreach($fillable as $fillableColumn)
@endforeach
@endif
@if($hasSoftDelete)use Illuminate\Database\Eloquent\SoftDeletes;
@endif
@if (isset($relations['belongsToMany']) && count($relations['belongsToMany']))
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
@endif
@if($hasRoles)use Spatie\Permission\Traits\HasRoles;
@endif

class {{ $modelBaseName }} extends Model
{
@if($hasSoftDelete)
    use SoftDeletes;
@endif
@if($hasRoles)use HasRoles;
@endif
    @if (!is_null($tableName))protected $table = '{{ $tableName }}';

    @endif
@if ($fillable)protected $fillable = [
    @foreach($fillable as $f)
    '{{ $f }}',
    @endforeach

    ];
    @endif

    @if ($hidden && count($hidden) > 0)protected $hidden = [
    @foreach($hidden as $h)
    '{{ $h }}',
    @endforeach

    ];
    @endif

    @if ($dates)protected $dates = [
    @foreach($dates as $date)
    '{{ $date }}',
    @endforeach

    ];
    @endif
@if (!$timestamps)public $timestamps = false;
    @endif

    protected $appends = ["api_route"];

    /* ************************ ACCESSOR ************************* */

    public function getApiRouteAttribute() {
        return route("api.{{ $routeBaseName }}.index");
    }
    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
@if (count($relations))

    /* ************************ RELATIONS ************************ */
@if(isset($relations['belongsTo']) && count($relations['belongsTo']))
@foreach($relations["belongsTo"] as $belongsTo)
    /**
    * Many to One Relationship to {{$belongsTo["related_model"]}}
    * {{'@'}}return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function {{$belongsTo['function_name']}}() {
        return $this->belongsTo({{$belongsTo['related_model']}},"{{$belongsTo['foreign_key']}}","{{$belongsTo["owner_key"]}}");
    }
@endforeach
@endif
@if (isset($relations["belongsToMany"]) && count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)
    /**
    * Relation to {{ $belongsToMany['related_model_name_plural'] }}
    *
    * {{'@'}}return BelongsToMany
    */
    public function {{ Str::camel($belongsToMany['related_table']) }}() {
        return $this->belongsToMany({{ $belongsToMany['related_model_class'] }}, '{{ $belongsToMany['relation_table'] }}', '{{ $belongsToMany['foreign_key'] }}', '{{ $belongsToMany['related_key'] }}');
    }
@endforeach

@endif
@endif}

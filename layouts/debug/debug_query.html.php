<?php

$highlighter = function($query, $prefix = '#__')
{
    $specialKeys = 'SELECT|FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND|CASE';
    $newlineKeywords = '#\b('.$specialKeys.')\b#i';

    $query = str_replace(array_map(function($word){
        return $word.' ';
    }, explode('|', strtolower($specialKeys.'|AS'))), array_map(function($word){
        return $word. ' ';
    },explode('|', $specialKeys.'|AS')), $query);

    $query = htmlspecialchars($query, ENT_QUOTES);

    $query = preg_replace($newlineKeywords, '<br />&#160;&#160;\\0', $query);

    $regex = array(

        // Tables are identified by the prefix.
        '/(=)/'                                        => '<b class="dbg-operator">$1</b>',

        // All uppercase words have a special meaning.
        '/(?<!\w|>)([A-Z_]{2,})(?!\w)/x'               => '<span class="dbg-command">$1</span>',

        // Tables are identified by the prefix.
        '/(' . $prefix . '[a-z_0-9]+)/' => '<span class="dbg-table">$1</span>',

    );

    $query = preg_replace(array_keys($regex), array_values($regex), $query);

    $query = str_replace('*', '<b style="color: red;">*</b>', $query);

    return $query;
}


?>
<div style="margin: 0 0 5px;" id="dbg-eloquent-<?= $index ?>">
    <?= $index+1 ?>.
    <span class="dbg-query-time">Query Time:
        <span class="label label-success"><?= $query['time'] ?>&nbsp;ms</span>
    </span>
<!--    <span class="dbg-query-memory">-->
<!--        Query memory: -->
<!--        <span class="label label-success">0.023&nbsp;MB</span> -->
<!--        Memory before query: -->
<!--        <span class="label label-default">1.179&nbsp;MB</span>-->
<!--    </span> -->
<!--    <span class="dbg-query-rowsnumber">Rows returned: -->
<!--        <span class="label label-success">1</span>-->
<!--    </span>-->
</div>
<?php
$string = $query['query'];
$replacements = $query['bindings'];
$string = preg_replace_callback('/\?/', function($matches) use (&$replacements) {
    return array_shift($replacements);
}, $string);
$string = $highlighter($string,$prefix);
?>
<pre>
    <?= $string ?>
</pre>
<?php
$duplicates = array_filter($log, function ($logItem)use($query){
    return $logItem['query'] == $query['query'] && $logItem['bindings'] == $query['bindings'];
});
if(isset($duplicates[$index])){
    unset($duplicates[$index]);
}
if($duplicates){
    ?>
    <div class="alert alert-error"><h4><?= $count = count($duplicates) ?> duplicate found!</h4>
        <div><?= $count ?> duplicates:
            <?php foreach ($duplicates as $i => $duplicate){
                ?>
                <a class="alert-link" href="#dbg-eloquent-<?= $i+1 ?>">#<?= $i+1 ?> [<?= $duplicate['time'] ?>ms]</a>
                &nbsp;
                <?php
            } ?>
        </div>
    </div>
    <?php
}

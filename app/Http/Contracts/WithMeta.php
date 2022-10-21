<?php


namespace App\Http\Contracts;


interface WithMeta
{
    // sync updating meta items with existing object
    public function syncMetaWithRequest();

    // merge meta as normal attributes of model object
    public function mergeRawMeta();

    // merge meta in a contracted format loading additional data about meta column
    public function mergeMeta($relationship);

    // get the name of the meta relation method
    public function getMetaRelation(): string;

    // get the changing attributes merged from normal attributes and meta attributes
    public function getAllDirty(): array;
}

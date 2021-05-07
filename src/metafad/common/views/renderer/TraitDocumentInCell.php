<?php
trait metafad_common_views_renderer_TraitDocumentInCell
{
    /**
     * @var null|object
     */
    protected $document;

    /**
     * @param object $document
     * @return void
     */
    public function setRecordDocument($document)
    {
        $this->document = $document;
    }
}

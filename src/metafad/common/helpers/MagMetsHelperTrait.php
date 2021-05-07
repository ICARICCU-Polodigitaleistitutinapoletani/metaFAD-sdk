<?php
trait metafad_common_helpers_MagMetsHelperTrait
{
    public function getStrumag($id)
    {
        $linkedStru = new stdClass();
        $stru = pinax_ObjectFactory::createModelIterator('metafad.strumag.models.Model')
            ->where('document_id', $id)->first();

        if ($stru) {
            $stru->getRawData();
            $linkedStru->physicalSTRU = $stru->physicalSTRU;
            $linkedStru->logicalSTRU = $stru->logicalSTRU;

            return $linkedStru;
        } else {
            return false;
        }
    }

    public function getLinkedStruMag($linkedStruMag)
    {
        $linkedStru = new stdClass();
        $linkedStru->id = $linkedStruMag->id;
        $linkedStru->text = $linkedStruMag->text;

        $stru = $this->getStrumag($linkedStru->id);
        if ($stru) {
            $this->images = json_decode($stru->physicalSTRU)->image;
        }

        return $linkedStru;
    }
}

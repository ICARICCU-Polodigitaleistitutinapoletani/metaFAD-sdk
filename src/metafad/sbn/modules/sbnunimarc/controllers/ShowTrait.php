<?php
trait metafad_sbn_modules_sbnunimarc_controllers_ShowTrait
{
    public function enableJSTab($datas)
    {
        //TODO mettere eventualmente nel config_common
        $schemaUnimarc = array('bibliographicLevel_tab' => array('bibliographicLevel', 'documentType', 'identificationCode',
            'ISBN', 'ISSN', 'print', 'ISMN', 'otherStandardNum',
            'NBN', 'musicEditorialNumber', 'ean',"title", "edition", "numeration",
                "presentation", "publication", "location", "phisicalDescription",
                "seriesCollectionDescription", "generalNotes", "titlesNotes",
                "responsabilityNotes", "exampleNotes", "periodicityNote",
                "contentNotes", "abstract", "electronicResourceNotes"),
            'elaborationType_tab' => array("elaborationType", "language", "country", "cdMonographic",
                "cdPeriodic", "codedDataGraphic", "codedDataCartographic",
                "codedDataCartographicCar", "cdMusicPrint", "cdElaboration",
                "cdOldMaterial", "cdExpressionContent", "cdSupportType"),
            'collection_tab' => array("collection", "continuationOf", "continuationInPartOf", "continueWith", "splitIn",
                "attachedTo", "otherEditionSameSupport", "translationOf", "set",
                "subset", "analiticPartBond", "examinationBond", "otherTitleRelated"),
            'titleUniform_tab' => array("titleUniform", "titleParallel", "titleAlternative", "titleKey", "titleFictitious"),
            'subject_tab' => array("subject", "publicationLocationNormalized", "deweyClassification",
                "deweyCode", "deweyDescription"),
            'pnMainResponsability_tab' => array("pnMainResponsability", "pnAlternativeResponsability",
                "pnSecondaryResponsability", "gnMainResponsability", "gnAlternativeResponsability",
                "gnSecondaryResponsability", "pnNotAccepted", "gnNotAccepted"),
            'recordOrigin_tab' => array("inventoryCollectionCopiesBE",
                "localization","originNotes", "monographyNumber"),
            'editorialMark_tab' => array("editorialMark", "rapresentation", "interpreters", "cdUniformTitleMusic", "composition")
        );

        foreach ($datas as $key => $value) {
            foreach ($schemaUnimarc as $k => $v)
                if (in_array($key, $v)) {
                    $this->view->getComponentById($k)->setAttribute('enabled', 'true');
                    $this->view->getComponentById($k)->setAttribute('visible', 'true');
                }
        }
    }
}
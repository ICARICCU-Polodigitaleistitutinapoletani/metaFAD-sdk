<?php

/**
 * Created by PhpStorm.
 * User: marco
 * Date: 13/02/17
 * Time: 9.39
 */
class metafad_importer_services_xmlArchiveEADEAC_utils_PipelineProvider
{
  public static function getEACPipeline($filePath, $instituteKey, $jsonMaps)
  {
    return <<<EOF
[
  {
    "obj": "metafad_common_importer_operations_ReadXML",
    "weight": 110,
    "params": {
      "filename": "$filePath"
    }
  },
  {
    "obj": "metafad_common_importer_operations_InstituteSetter",
    "weight": 10,
    "params": {
      "ignoreInput": true,
      "instituteKey": "$instituteKey"
    }
  },
  {
    "obj": "metafad_common_importer_operations_GetXMLNodeList",
    "weight": 250,
    "params": {
      "rootxpath": "/eacgrp/xw_doc/eac-cpf"
    }
  },
  {
    "obj": "metafad_common_importer_operations_Iterate",
    "weight": 6000,
    "params": {
      "operations": [
        {
          "obj": "metafad_common_importer_operations_EACInferModel",
          "params": {
            "xpathToMappingFile": {
              "self::node()[descendant::entityType[text()[normalize-space(.)='person']] and descendant::function[text()[normalize-space(.)='soggetto produttore']]]": "{$jsonMaps['entitaPersona']}",
              "self::node()[descendant::entityType[text()[normalize-space(.)='corporateBody']] and descendant::function[text()[normalize-space(.)='soggetto produttore']]]": "{$jsonMaps['entitaFamiglia']}",
              "self::node()[descendant::entityType[text()[normalize-space(.)='family']] and descendant::function[text()[normalize-space(.)='soggetto produttore']]]": "{$jsonMaps['entitaEnte']}",
              "self::node()[descendant::entityType[text()[normalize-space(.)='family' or normalize-space(.)='person']]]": "{$jsonMaps['antroponimo']}",
              "self::node()[descendant::entityType[text()[normalize-space(.)='corporateBody']]]": "{$jsonMaps['ente']}"
            }
          }
        },
        {
          "obj": "metafad_common_importer_operations_XmlToJson",
          "params": {
            "schemafile": "{$jsonMaps['void']}"
          }
        },
        {
          "obj": "metafad_common_importer_operations_ResolveMAG",
          "params": {}
        },
        {
          "obj": "metafad_common_importer_operations_SaveArchivistico"
        },
        {
          "obj": "metafad_common_importer_operations_LogInput",
          "params": {
            "instructions": {
              "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>, intestazione:<##head##>",
              "valueSrc": {
                "id": "id",
                "head": "data->intestazione",
                "model": "data->__model",
                "extid": "data->externalID"
              }
            }
          }
        }
      ]
    }
  },
  {
    "obj": "metafad_common_importer_operations_FlushQueue",
    "weight": 10
  }
]
EOF;
  }

  /**
   * Restituisce la pipeline testuale
   * @param $filePath
   * @param $instituteKey
   * @param $jsonMaps
   * @param $fondoId string|int se 0 o null, non viene creato un fondo wrapper
   * @param $fondoText
   * @param null $dumpDir
   * @return string
   */
  public static function getEADPipeline($filePath, $instituteKey, $jsonMaps, $fondoId, $fondoText, $dumpDir = null)
  {
    $dumpRow = realpath($dumpDir) ? "\"dumpdir\": \"$dumpDir\"," : "";

    $linkOperation = $fondoId ? "        {
          \"obj\": \"metafad_common_importer_operations_MergeObject\",
          \"params\": {
            \"overwrite\": false,
            \"object\": {
              \"parent\": {
                \"id\": \"$fondoId\",
                \"text\": \"$fondoText\"
              }
            }
          }
        }," : "";


    return <<<EOF
[
  {
    "obj": "metafad_common_importer_operations_ReadXML",
    "weight": 110,
    "params": {
      "filename": "$filePath"
    }
  },
  {
    "obj": "metafad_common_importer_operations_InstituteSetter",
    "weight": 10,
    "params": {
      "ignoreInput": true,
      "instituteKey": "$instituteKey"
    }
  },
  {
    "obj": "metafad_common_importer_operations_EADGetXMLNodeList",
    "weight": 400,
    "params": {
      "idxpath": "./@id",
      "rootxpath": "/rsp/dsc/c",
      "childxpath": "./dsc/c",
      $dumpRow
      "acronimoSistema": "PDN"
    }
  },
  {
    "obj": "metafad_common_importer_operations_Iterate",
    "weight": 7000,
    "params": {
      "operations": [
        {
          "obj": "metafad_common_importer_operations_EADXmlToJson",
          "params": {
            "schemafile": "{$jsonMaps['common']}",
            "ca_schemafile": "{$jsonMaps['CA']}",
            "ua_schemafile": "{$jsonMaps['UA']}",
            "ud_schemafile": "{$jsonMaps['UD']}"
          }
        },
{$linkOperation}
        {
          "obj": "metafad_common_importer_operations_EADPreSaveCorrections"
        },
        {
          "obj": "metafad_common_importer_operations_SaveArchivistico"
        },
        {
          "obj": "metafad_common_importer_operations_LogInput",
          "params": {
            "instructions": {
              "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
              "valueSrc": {
                "id": "id",
                "model": "data->__model",
                "extid": "data->externalID"
              }
            }
          }
        }
      ]
    }
  },
  {
    "obj": "metafad_common_importer_operations_FlushQueue",
    "weight": 10
  }
]
EOF;
  }

  public static function getEAD3Pipeline($filePath, $instituteKey, $jsonMaps, $fondoId, $fondoText, $acronimoSistema, $configFile, $logFile, $partialValidation, $overwriteScheda, $onlyValidation, $recordId, $onlyRecord, $onlyMedia, $dumpDir = null)
  {
    if($onlyValidation)  {
    return  "[
      {
        \"obj\": \"metafad_common_importer_operations_ReadXMLIcarImport\",
        \"params\": {
          \"validateAgainstXSD\": true,
          \"validateRelations\": true,
          \"onlyValidation\": true,
          \"pathsAndValueValidation\": true,
          \"logFile\": \"$logFile\",
          \"filename\": \"$filePath\",
          \"partialValidation\": \"$partialValidation\",
          \"config_file\": \"$configFile\",
          \"pathsValidation\": \"{$jsonMaps['pathsValidation']}\"
        }
      }
      ]";
    }

    if($onlyMedia == '1' && $onlyRecord == '0')  {
      return  "[
        {
          \"obj\": \"metafad_common_importer_operations_ImageImporter\",
          \"params\": {
            \"logFile\": \"$logFile\",
            \"filename\": \"$filePath\",
            \"instituteKey\": \"$instituteKey\",
            \"overwrite\": \"$overwriteScheda\",
            \"daoFile\": \"{$jsonMaps['DAO']}\",
            \"importMedia\": \"$onlyRecord\"
          }
        }
        ]";
      }
    
    $dumpRow = realpath($dumpDir) ? "\"dumpdir\": \"$dumpDir\"," : "";

    $linkOperation = $fondoId ? "        {
        \"obj\": \"metafad_common_importer_operations_MergeObject\",
        \"params\": {
          \"overwrite\": false,
          \"object\": {
            \"parent\": {
              \"id\": \"$fondoId\",
              \"text\": \"$fondoText\"
            }
          }
        }
      }," : "";


    return <<<EOF
[
{
  "obj": "metafad_common_importer_operations_ReadXMLIcarImport",
  "weight": 110,
  "params": {
    "filename": "$filePath",
    "validateAgainstXSD": true,
    "validateRelations": true,
    "pathsAndValueValidation": true,
    "pathsValidation" : "{$jsonMaps['pathsValidation']}",
    "config_file" : "$configFile",
    "logFile": "$logFile",
    "partialValidation": "$partialValidation",
    "type": "ead3Hierarchic",
    "prefix": "ead"
  }
},
{
  "obj": "metafad_common_importer_operations_InstituteSetter",
  "weight": 10,
  "params": {
    "ignoreInput": true,
    "instituteKey": "$instituteKey"
  }
},
{
  "obj": "metafad_common_importer_operations_EAD3GetXMLNodeList",
  "weight": 400,
  "params": {
    "idxpath": "./did/unitid/@identifier",
    "rootxpath": "/ead/archdesc",
    "childxpath": "./dsc/c",
    $dumpRow
    "acronimoSistema": "$acronimoSistema",
    "recordId": "$recordId",
    "config_file" : "$configFile"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 7000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_EADXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['common']}",
          "ca_schemafile": "{$jsonMaps['CA']}",
          "ua_schemafile": "{$jsonMaps['UA']}",
          "ud_schemafile": "{$jsonMaps['UD']}",
          "config_file" : "$configFile",
          "config_node": "ead3",
          "overwrite": "$overwriteScheda",
          "namespaces" : {"ead" : "http://ead3.archivists.org/schema/"},
          "logFile": "$logFile"
        }
      },
{$linkOperation}
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations"
      },
      {
        "obj": "metafad_common_importer_operations_EAD3PreSaveCorrections",
        "params": {
        }
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRoot",
        "params": {
          "overwrite": "$overwriteScheda"
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_FlushQueue",
  "weight": 10
},
{
  "obj": "metafad_common_importer_operations_ReadXMLIcarImport",
  "weight": 110,
  "params": {
    "filename": "$filePath",
    "type": "scons",
    "prefix": "scons"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 1000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_ImportXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['SCONS']}",
          "config_file" : "$configFile",
          "config_node": "scons2",
          "acronimoSistema": "$acronimoSistema",
          "overwrite": "$overwriteScheda"
        }
      },
{$linkOperation}
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations",
        "params": {
          "type": "produttoreConservatore"
        }
      },
      {
        "obj": "metafad_common_importer_operations_SCONSPresaveCorrections"
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRelations",
        "params": {
          "model": "archivi.models.ProduttoreConservatore",
          "overwrite": "$overwriteScheda"
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_ReadXMLIcarImport",
  "weight": 110,
  "params": {
    "filename": "$filePath",
    "type": "eac-cpf",
    "prefix": "eac-cpf"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 1000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_ImportXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['EAC-CPF']}",
          "config_file" : "$configFile",
          "config_node": "eac-cpf",
          "acronimoSistema": "$acronimoSistema",
          "overwrite": "$overwriteScheda"
        }
      },
{$linkOperation}
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations",
        "params": {
          "type": "produttoreConservatore"
        }
      },
      {
        "obj": "metafad_common_importer_operations_EACCPFPresaveCorrections"
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRelations",
        "params": {
          "model": "archivi.models.ProduttoreConservatore",
          "charToFix": "_",
          "overwrite": "$overwriteScheda"        
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_ReadXMLIcarImport",
  "weight": 110,
  "params": {
    "filename": "$filePath",
    "type": "ead-strumenti",
    "prefix": "ead"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 1000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_ImportXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['EAD-Strumenti']}",
          "config_file" : "$configFile",
          "config_node": "ead3_strumenti",
          "acronimoSistema": "$acronimoSistema",
          "overwrite": "$overwriteScheda"
        }
      },
{$linkOperation}
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations",
        "params": {
          "type": "strumento"
        }
      },
      {
        "obj": "metafad_common_importer_operations_StrumentiPresaveCorrections"
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRelations",
        "params": {
          "model": "archivi.models.SchedaStrumentoRicerca",
          "overwrite": "$overwriteScheda"        
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_CleanExternalID",
  "weight": 100,
  "params": {
    "filename": "$filePath",
    "overwrite": "$overwriteScheda"
  }
},
{
  "obj": "metafad_common_importer_operations_RegenerateComplessiLink",
  "weight": 100,
  "params": {
    "filename": "$filePath"
  }
},
{
  "obj": "metafad_common_importer_operations_ImageImporter",
  "weight": 10000,
  "params": {
    "instituteKey": "$instituteKey",
    "filename": "$filePath",
    "overwrite": "$overwriteScheda",
    "logFile": "$logFile",
    "daoFile": "{$jsonMaps['DAO']}",
    "importMedia": "$onlyRecord"
  }
}
]
EOF;
  }

  public static function getSiasSiusaPipeline($url, $instituteKey, $jsonMaps, $acronimoSistema, $configFile, $logFile, $type, $idsRelations, $recordId = null)
  {
    return <<<EOF
[
{
  "obj": "metafad_common_importer_operations_ReadXMLResponse",
  "weight": 110,
  "params": {
    "url": "$url",
    "pathsAndValueValidation": true,
    "pathsValidation" : "{$jsonMaps['pathsValidation']}",
    "config_file" : "$configFile",
    "logFile": "$logFile",
    "prefix": "ead_icar",
    "idsEac": "{$idsRelations['eac_icar']}",
    "idsScons": "{$idsRelations['scons_icar']}",
    "idsSdc": "{$idsRelations['ead_icar']}",
    "type": "$type"


  }
},
{
  "obj": "metafad_common_importer_operations_InstituteSetter",
  "weight": 10,
  "params": {
    "ignoreInput": true,
    "instituteKey": "$instituteKey"
  }
},
{
  "obj": "metafad_common_importer_operations_EAD3GetXMLNodeList",
  "weight": 400,
  "params": {
    "idxpath": "./did/unitid/@identifier",
    "rootxpath": "/ead/archdesc",
    "childxpath": "./dsc/c",
    "acronimoSistema": "$acronimoSistema",
    "config_file" : "$configFile",
    "logFile": "$logFile",
    "recordId": "$recordId"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 2000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_EADXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['common']}",
          "ca_schemafile": "{$jsonMaps['CA']}",
          "ua_schemafile": "{$jsonMaps['UA']}",
          "ud_schemafile": "{$jsonMaps['UD']}",
          "config_file" : "$configFile",
          "config_node": "ead3",
          "overwrite": true,
          "namespaces" : {"ead" : "http://ead3.archivists.org/schema/"},
          "logFile": "$logFile"
        }
      },
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations"
      },
      {
        "obj": "metafad_common_importer_operations_EAD3PreSaveCorrections",
        "params": {
        }
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRoot",
        "params": {
          "overwrite": true,
          "oaiImport": true,
          "oaiId": "$url",
          "type": "$type"
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_FlushQueue",
  "weight": 10
},
{
  "obj": "metafad_common_importer_operations_ReadXMLResponseRelations",
  "weight": 110,
  "params": {
    "type": "$type",
    "prefix": "eac_icar",
    "pathsAndValueValidation": true,
    "pathsValidation" : "{$jsonMaps['pathsValidation']}",
    "config_file" : "$configFile",
    "logFile": "$logFile",
    "ids": "{$idsRelations['eac_icar']}"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 1000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_ImportXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['EAC-CPF']}",
          "config_file" : "$configFile",
          "config_node": "eac-cpf",
          "acronimoSistema": "$acronimoSistema",
          "overwrite": true
        }
      },
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations",
        "params": {
          "type": "produttoreConservatore"
        }
      },
      {
        "obj": "metafad_common_importer_operations_EACCPFPresaveCorrections"
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRelations",
        "params": {
          "model": "archivi.models.ProduttoreConservatore",
          "overwrite": true,
          "oaiImport": true,
          "type": "$type",
          "prefixOAI": "eac_icar"
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_FlushQueue",
  "weight": 10
},
{
  "obj": "metafad_common_importer_operations_ReadXMLResponseRelations",
  "weight": 110,
  "params": {
    "type": "$type",
    "prefix": "scons_icar",
    "pathsAndValueValidation": true,
    "pathsValidation" : "{$jsonMaps['pathsValidation']}",
    "config_file" : "$configFile",
    "logFile": "$logFile",
    "ids": "{$idsRelations['scons_icar']}"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 1000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_ImportXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['SCONS']}",
          "config_file" : "$configFile",
          "config_node": "scons2",
          "acronimoSistema": "$acronimoSistema",
          "overwrite": true
        }
      },
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations",
        "params": {
          "type": "produttoreConservatore"
        }
      },
      {
        "obj": "metafad_common_importer_operations_SCONSPresaveCorrections"
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRelations",
        "params": {
          "model": "archivi.models.ProduttoreConservatore",
          "overwrite": true,
          "oaiImport": true,
          "type": "$type",
          "prefixOAI": "scons_icar"
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_FlushQueue",
  "weight": 10
},
{
  "obj": "metafad_common_importer_operations_ReadXMLResponseRelations",
  "weight": 110,
  "params": {
    "type": "$type",
    "prefix": "ead_icar",
    "pathsAndValueValidation": true,
    "pathsValidation" : "{$jsonMaps['pathsValidation']}",
    "config_file" : "$configFile",
    "logFile": "$logFile",
    "ids": "{$idsRelations['ead_icar']}"
  }
},
{
  "obj": "metafad_common_importer_operations_Iterate",
  "weight": 1000,
  "params": {
    "operations": [
      {
        "obj": "metafad_common_importer_operations_ImportXmlToJson",
        "params": {
          "schemafile": "{$jsonMaps['EAD-Strumenti']}",
          "config_file" : "$configFile",
          "config_node": "ead3_strumenti",
          "acronimoSistema": "$acronimoSistema",
          "overwrite": true
        }
      },
      {
        "obj": "metafad_common_importer_operations_DateEncodingOperations",
        "params": {
          "type": "strumento"
        }
      },
      {
        "obj": "metafad_common_importer_operations_StrumentiPresaveCorrections"
      },
      {
        "obj": "metafad_common_importer_operations_SaveArchivisticoRelations",
        "params": {
          "model": "archivi.models.SchedaStrumentoRicerca",
          "overwrite": true,
          "oaiImport": true,
          "type": "$type",
          "prefixOAI": "ead_icar"
        }
      },
      {
        "obj": "metafad_common_importer_operations_LogInput",
        "params": {
          "instructions": {
            "message": "Salvato ID_DB: <##id##>, Model:<##model##>, extid:<##extid##>",
            "valueSrc": {
              "id": "id",
              "model": "data->__model",
              "extid": "data->externalID"
            }
          }
        }
      }
    ]
  }
},
{
  "obj": "metafad_common_importer_operations_FlushQueue",
  "weight": 10
},
{
  "obj": "metafad_common_importer_operations_RegenerateComplessiLink",
  "weight": 100,
  "params": {
    "url": "$url"
  }
}
]
EOF;
  }
}
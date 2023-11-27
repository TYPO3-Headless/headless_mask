<?php

namespace NITSAN\NsHeadlessMask\Utility;

class MaskElementsUtility
{
    public function setupComponentWiseTypoScript(): string {
        $tsStringComponent = '';
        $elements = $this->getElements();
        foreach ($elements as $theComponent => $elems) {
    
            $tsStringForFields = '';
            $fieldTsConfig = '';
            if ($fields = $elems['columnsOverrides']){
                foreach ($fields as $fieldKey => $field) {
                    $fieldName = $field['label'] ? $this->dashesToCamelCase($field['label']) : $fieldKey;
                    $fieldType = $this->getFieldType($fieldKey);
                    if (!key_exists($fieldKey, $GLOBALS['TCA'])) {
                        $fieldTsConfig .= $this->getFieldTypoScript($fieldType, $fieldKey, $fieldName);
                    }
                    
                    if (key_exists($fieldKey, $GLOBALS['TCA'])) {
                        $nestedFieldTsConfig = '';
                        $nestedFields = $this->getNestedFields($GLOBALS['TCA'][$fieldKey]);
                        foreach($nestedFields as $nestedFieldKey => $nestedField) {
                            $nestedFieldName = $nestedField['label'] ? $this->dashesToCamelCase($nestedField['label']) : $nestedFieldKey;
                            $nestedFieldTsConfig .= $this->getFieldTypoScript($nestedField['type'], $nestedFieldKey, $nestedFieldName);
                        }

                        $tsStringForFields .="
                            $fieldName = TEXT
                            $fieldName {
                                dataProcessing {
                                    10 = FriendsOfTYPO3\Headless\DataProcessing\DatabaseQueryProcessor
                                    10 {
                                        table = $fieldKey
                                        where.field = uid
                                        pidInList.field = pid
                                        where.intval = 1
                                        where.dataWrap = parentid = |
                                        orderBy = sorting
                                        as = content
                                        fields {
                                           $nestedFieldTsConfig
                                        }
                                    }
                                }
                            }
                        ";
                    }
                }
                $tsStringComponent .=
                      $theComponent." =< lib.contentElementWithHeader
                    ".$theComponent." {
                        fields {
                            content {
                                fields {"
                                    .$fieldTsConfig.
                                    $tsStringForFields."
                                }
                            }
                        }
                    }
                ";
            }
        }
        return 'tt_content {'."\n"
            .$tsStringComponent.
        "\n".'}';
    }

    private function getElements(): array
    {
        $elements = [];
        if ($contents = $GLOBALS['TCA']['tt_content']['types']) {
            foreach ($contents as $cType => $content) {
                if (str_contains($cType, 'mask_')) {
                    $elements[$cType] = $content;
                }
            }
        }
        return $elements;
    }

    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace([' ', '-'], '', ucwords(str_replace(' ', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    private function getNestedFields(mixed $nestedTCA)
    {
        $fields = [];
        foreach ($nestedTCA['columns'] as $fieldKey => $field) {
            if (str_contains($fieldKey, 'tx_mask_')) {
                $fields[$fieldKey]['type'] = $field['config']['type'];
                $fields[$fieldKey]['label'] = $field['label'];
            }
        }
        return $fields;

    }

    private function getFieldType(string $fieldKey)
    {
        return $GLOBALS['TCA']['tt_content']['columns'][$fieldKey]['config']['type'];
    }

    private function getFieldTypoScript(string $fieldType, string $fieldKey, string $fieldName)
    {
        $fieldName = preg_replace('/[^a-zA-Z0-9_ -]/s','',$fieldName);
        return match ($fieldType) {
            'file', 'media', 'image' => "
                $fieldName = TEXT
                $fieldName {
                    dataProcessing {
                        10 = FriendsOfTYPO3\Headless\DataProcessing\FilesProcessor
                        10 {
                            references.fieldName = $fieldKey
                            as = image
                            processingConfiguration {
                                delayProcessing = 1
                            }
                        }
                    }
                }
            ",
            'link' => "
                $fieldName = TEXT
                $fieldName {
                    field = $fieldKey
                    as = link
                    typolink {
                        parameter {
                            field = $fieldKey
                        }
                        returnLast = result
                    }
                }
            ",
            default => $this->getNestedFieldTyposcript($fieldKey, $fieldName),
        };
    }

    private function getNestedFieldTyposcript(string $fieldKey, string $fieldName) {
        if(key_exists($fieldKey, $GLOBALS['TCA'])) {
            $childFieldsTsConfig = '';
            $tsStringForFields = '';
            $nestedChildFields = $this->getNestedFields($GLOBALS['TCA'][$fieldKey]);

                foreach($nestedChildFields as $childKey => $childFieldsValue) {
                    $childNestedFieldName = $childFieldsValue['label'] ? $this->dashesToCamelCase($childFieldsValue['label']) : $childKey;
                    $childFieldsTsConfig .= $this->getFieldTypoScript($childFieldsValue['type'], $childKey, $childNestedFieldName);
                    $childFieldsTsConfig .= $this->getChildFieldTyposcript($childFieldsValue['type'], $childKey, $childNestedFieldName);
                }

                $tsStringForFields .="
                $fieldName = TEXT
                $fieldName {
                    dataProcessing {
                        10 = FriendsOfTYPO3\Headless\DataProcessing\DatabaseQueryProcessor
                        10 {
                            table = $fieldKey
                            where.field = uid
                            pidInList.field = pid
                            where.intval = 1
                            where.dataWrap = parentid = |
                            orderBy = sorting
                            as = content
                            fields {
                                $childFieldsTsConfig
                            }
                        }
                    }
                }
            ";
            return $tsStringForFields;
        } else {
            return "
                $fieldName = TEXT
                $fieldName {
                    field = $fieldKey
                    parseFunc =< lib.parseFunc_links
                }
            ";
        }
    }

    private function getChildFieldTyposcript(string $fieldType, string $fieldKey, string $fieldName) {
        $fieldName = preg_replace('/[^a-zA-Z0-9_ -]/s','',$fieldName);
        return match ($fieldType) {
            'file', 'media', 'image' => "
                $fieldName = TEXT
                $fieldName {
                    dataProcessing {
                        10 = FriendsOfTYPO3\Headless\DataProcessing\FilesProcessor
                        10 {
                            references.fieldName = $fieldKey
                            as = image
                            processingConfiguration {
                                delayProcessing = 1
                            }
                        }
                    }
                }
            ",
            'link' => "
                $fieldName = TEXT
                $fieldName {
                    field = $fieldKey
                    as = link
                    typolink {
                        parameter {
                            field = $fieldKey
                        }
                        returnLast = result
                    }
                }
            ",
            default => "
                $fieldName = TEXT
                $fieldName {
                    field = $fieldKey
                    parseFunc =< lib.parseFunc_links
                }
            ",
        };
    }
}
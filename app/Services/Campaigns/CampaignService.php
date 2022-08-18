<?php

namespace App\Services\Campaigns;

use IvoPetkov\HTML5DOMDocument;

class CampaignService
{
    protected static $imageNameTemp = null;

    public static function previewEmail($request, $array)
    {
        info('previewEmail: inside Fisrt');
        $imageName = self::$imageNameTemp;
        if (empty($imageName) && $request->hasFile('background_banner')){
            $request->validate(
                [
                    'background_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                ]
            );

            $name = time() . '.' . $request->background_banner->extension();
            $request->background_banner->move(public_path('uploads'), $name);
            self::$imageNameTemp = $name;
        }
        $image = self::$imageNameTemp;

        $bodyPreviewEmail = $request->preview_email;
        $cutBodyPreview = str_replace(array("\\",), '', $bodyPreviewEmail);
        $domBody = new HTML5DOMDocument();
        $domBody->loadHTML($cutBodyPreview);

        $querySelectorSubject = $domBody->querySelectorAll('.tiptap_variant');
        for ($i = 0; $i < count( $querySelectorSubject ); $i++){
            $nameVariant = $querySelectorSubject[$i]->attributes[2]->value;
            foreach ($array as $arr) {
                if ($nameVariant == $arr['variant']){
                    $querySelectorSubject[$i]->textContent = $arr['value'];
                    $querySelectorSubject[$i]->attributes[0]->value = "color: rgb(40, 41, 61); font-weight: 600; margin: 0px 3px;";
                }
            }
        }
        info('previewEmail: handle Body');

        if (!empty($image)) {
            $img = $domBody->getElementsByTagName('img')[0];
            $img->setAttribute('src', asset('uploads/' . $image));
        }
        info('previewEmail: Handle Image');

        $bodyEmail = $domBody->saveHTML();
        info('previewEmail: save body');

        return $bodyEmail;
    }

    public static function subject($request, $array)
    {
        $domSubject = new HTML5DOMDocument();
        $domSubject->loadHTML($request);
        $querySelectorSubject = $domSubject->querySelector('p')->childNodes;

        $arraySubject = [];
        foreach ($querySelectorSubject as $item) {
            if ($item->nodeName == '#text') {
                array_push($arraySubject, $item->data);
            } else {
                $aa = $item->childNodes[0]->data;
                array_push($arraySubject, $aa);
            }
        }
        $arrayJoinElements = implode(' ', $arraySubject);

        foreach ($array as $arr) {
            $arrayJoinElements = str_replace($arr['variant'], $arr['value'], $arrayJoinElements);
        }
        return $arrayJoinElements;
    }

}

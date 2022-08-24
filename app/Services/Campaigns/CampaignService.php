<?php

namespace App\Services\Campaigns;

use IvoPetkov\HTML5DOMDocument;

class CampaignService
{
    /**
     * Create variable image temp to save image receive from Request
     *
     * @var null
     */
    protected static $imageNameTemp = null;

    /**
     * Receive request, array from sendMail or SendMailPreview replace image, mailing content and put in dom
     *
     * @param object $request
     * @param object $array
     * @return string
     */
    public static function previewEmail($request, $array)
    {

        $imageName = self::$imageNameTemp;
        if (empty($imageName) && $request->hasFile('background_banner')){
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

        if (!empty($image)) {
            $img = $domBody->getElementsByTagName('img')[0];
            $img->setAttribute('src',config('shopify.ngrok').'/uploads/' . $image);
//             $img->setAttribute('src', asset('uploads/' . $image));
        }

        $bodyEmail = $domBody->saveHTML();

        return $bodyEmail;
    }

    /**
     * Receive request, array from SendMail or sendMailPreview put in dom and replace subject
     *
     * @param object $request
     * @param object $array
     * @return string
     */
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

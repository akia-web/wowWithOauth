<?php

namespace App\Controller;

use Exception;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MountController extends AbstractController
{
    #[Route('/montures', name: 'montures')]
    public function mount()
    {   
        $token = $_COOKIE["bnetToken"];
        $battleTag = $_COOKIE["battleTag"];
        // ----------------- TOUTES LES MONTURES ---------------------------------------------------
        $allMount = [];
        $nbAllMount=0;
        $accountMount = [];
        $nbAccountMount = 0;
        $allMountWithCountMount = [];

        try{

            $allMount =  (json_decode(file_get_contents("../json/allmounts.json")));
            $nbAllMount = count($allMount);        

        }catch(Exception $e){

        }       

        // --------------- MONTURES DU COMPTES ---------------------------
    
        
        try{
            $data = [];
            $infoMount =  (json_decode(file_get_contents("https://eu.api.blizzard.com/profile/user/wow/collections/mounts?namespace=profile-eu&locale=fr_FR&access_token=$token")))->{'mounts'};
            array_push($data, $infoMount);

            $nbAccountMount = count($data[0]);
            // dd($nbAccountMount);

            // créer un objet avec l'id et le name des montures
            for($i = 0; $i<$nbAccountMount; $i++){
               $idMount = $data[0][$i]->{'mount'}->{'id'};
                array_push($accountMount, $idMount);
            }
            // dd($accountMount);
            //compare les deux tableau

            for($i = 0; $i<$nbAllMount; $i++){
                $info = $allMount[$i];

                if(in_array($allMount[$i]->{'id'},$accountMount)){
                    $info->got = true;
                }else{
                    $info->got=false;
                }
                array_push($allMountWithCountMount, $info);
            }

          

        }catch(Exception $e){

              
               
               
              
        }

        file_put_contents("../json/$battleTag-montures.json", json_encode($allMountWithCountMount));
        


        //--------- verif si y'a image ou pas
        $imageManquante = [];
        for($i=0; $i<count($allMountWithCountMount); $i++){
            // dd($allMountWithCountMount);
            if(!property_exists($allMountWithCountMount[$i], 'image')){
                $idImageManquante = $allMountWithCountMount[$i]->{'id'};
                array_push($imageManquante, $idImageManquante);
            }
        }

        //  0 => 270
        //   1 => 457
        //   2 => 1053
        //   3 => 1436
        // dd($imageManquante);




        // $allMount =  (json_decode(file_get_contents("../json/$battleTag-mounts.json")));
        //   dd($allMountWithCountMount);
        

       return $this->render('mount/index.html.twig',[
        'nbAllMount' =>$nbAllMount,
        'nbAccountMount' => $nbAccountMount ,
        'allMontures'=>$allMountWithCountMount
       ]);
        
    }


    #[Route('/monture/{id}', name: 'add')]
    public function add($id){
        $token = $_COOKIE["bnetToken"];
        $allMount = json_decode(file_get_contents("../json/allMounts.json"));
        $data = [];
    //  dd($allMount);

        try{
          $response =   (json_decode(file_get_contents("https://eu.api.blizzard.com/data/wow/mount/$id?namespace=static-9.1.5_40764-eu&access_token=$token")));
          array_push($data, $response);
          
        //   dd($response);

          for($i=0; $i<count($allMount); $i++){
              if($allMount[$i]->{"id"} == $id){
                $allMount[$i]->creatureId = $data[0]->{'creature_displays'}[0]->{'id'};
                $idcreature=$allMount[$i]->creatureId;
                $allMount[$i]->image ="https://render-eu.worldofwarcraft.com/npcs/zoom/creature-display-$idcreature.jpg";
              }
          }


        }catch(Exception $e){

        }
        // dd($allMount);
        file_put_contents("../json/allMounts.json", json_encode($allMount));

        return $this->render('mount/add.html.twig',[
          
            // 'montures'=>$montures,
            // 'nbMontures'=>$nbMonture,
            
        ]);
    }
}

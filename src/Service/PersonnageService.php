<?php

namespace App\Service;

use Exception;
use stdClass;

class PersonnageService
{

    //Boucle pour récupérer le compte avec le plus de personnages
    public function getMainCount($nbCompte, $data){
        $comptesLePlusRempli="";
        $mainAccount=null;
        $comptes = [];
      
        for($j=0; $j<$nbCompte; $j++){
            array_push($comptes,count($data[0]->{'wow_accounts'}[$j]->{'characters'}));
            $comptesLePlusRempli=max($comptes);

            if($comptesLePlusRempli == count($data[0]->{'wow_accounts'}[$j]->{'characters'})){
                $mainAccount =  $data[0]->{'wow_accounts'}[$j]->{'characters'};
            }

            return $mainAccount;
        }
    }

    //Récupère les personnages

    public function getPersonnagesWithAPI($account, $token){
        $result = [];

        $numeroCompte = 0;
        for($i=0; $i<count($account); $i++){
           
            $compte = new stdClass();
            $compte->numeroCompte = "Compte $numeroCompte";
            $personnages=$account[$i]->{"characters"};
            $listePersonnages=[];
            $listePersonnagesFalse = [];
            // dd($account);
            for($j=0; $j<count($personnages); $j++){
                $info = new stdClass();
                $info->id = $personnages[$j]->{'id'};
                $info->name = $personnages[$j]->{'name'};
                $info->reaml = $personnages[$j]->{'realm'}->{'name'}->{'fr_FR'};
                $info->classe = $personnages[$j]->{'playable_class'}->{'name'}->{'fr_FR'};
                $info->race = $personnages[$j]->{'playable_race'}->{'name'}->{'fr_FR'};
                $info->faction = $personnages[$j]->{'faction'}->{'name'}->{'fr_FR'};
                $info->level = $personnages[$j]->{'level'};
                $info->urlPersonnage=$personnages[$j]->{'character'}->{'href'};
                $info->imagePetite = "";
                $info->moyenneFace="";
                $info->grande="";
                $info->grandePNG="";
                $info->personnageValide=true;
                try{
                    $resultUrlImage=  json_decode(file_get_contents("$info->urlPersonnage&access_token=$token"));
                    $info->urlImage =$resultUrlImage->{'media'}->{'href'};
                    try{
                        $resultImage = json_decode(file_get_contents("$info->urlImage&access_token=$token"));
                        $info->imagePetite = $resultImage->{'assets'}[0]->{'value'};
                        $info->moyenneFace=$resultImage->{'assets'}[1]->{'value'};
                        $info->grande=$resultImage->{'assets'}[2]->{'value'};
                        $info->grandePNG=$resultImage->{'assets'}[3]->{'value'};
                    }catch(Exception $e){
                        $info->personnageValide=false;
                        $info->images="pas d'images";
                    }
                    
                }catch(Exception $e){
                    $info->personnageValide=false;
                    $info->urlImage="Pas de lien vers les medias";
                }
            
                if($info->personnageValide==true){
                    
                    array_push($listePersonnages, $info);
                }else{
                    array_push($listePersonnagesFalse,$info);
                }
                
            }
            // if(count($listePersonnages)>0){
                $numeroCompte++;
                array_push($result, $compte);
                $compte->numeroCompte = $numeroCompte;
                if(count($listePersonnages)>0){
                    $compte->personnages = $listePersonnages;
                }else{
                    $compte->personnages = [];
                }

                if(count($listePersonnagesFalse)>0){
                    $compte->personnagesInvalide = $listePersonnagesFalse;
                }else{
                    $compte->$listePersonnagesFalse = [];
                }
                
            // }
           


        }
    //   dd($result);
        return $result;
    }


   
}
<?php

namespace App\Controller;

use App\Service\PersonnageService;
use Exception;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonnageController extends AbstractController
{
    #[Route('/personnages', name: 'personnages')]
    public function index(PersonnageService $personnageService): Response
    {
        
        if(isset($_COOKIE["bnetToken"]) || !isset($_COOKIE["battleTag"])){
            $token = $_COOKIE["bnetToken"];
            $urlRedirect = urldecode("http://127.0.0.1:8000/battlenetConnexion");
            $battleTag = $_COOKIE["battleTag"];
            $tableau = [];
            $characters=[];

            //test du token
            try{
                json_decode(file_get_contents("https://eu.battle.net/oauth/userinfo?access_token=$token"))->{'battletag'};
            }catch(Exception $e){

                return $this->redirectToRoute("battlenet");
            }
           

            // si les données ont déjà été obtenu on les prends sinon on les créées.
            if(file_exists("../json/$battleTag-info-personnages.json")){
            

               $characters = json_decode(file_get_contents("../json/$battleTag-info-personnages.json"));

            }else{  
                try{

                    // -----------------requete pour avoir la liste des personnages du compte--------------------
                    $result= json_decode(file_get_contents("https://eu.api.blizzard.com/profile/user/wow?access_token=$token&namespace=profile-eu")); 
                    array_push($tableau, $result);
                    // dd($result);
                    $accounts =$tableau[0]->{"wow_accounts"};
                               
                    //----------- debug de la requete pour récuperer les personnages ------------------------------
              
                    $characters = $personnageService->getPersonnagesWithAPI($accounts,$token);
                    
                    file_put_contents("../json/$battleTag-info-personnages.json", json_encode($characters));
                    
                 

                }catch(Exception $e){
                    
                    return $this->redirectToRoute("battlenet");
                }
        
                
            }
           
            // boucle pour récuperer les personnages invalides
            $personnagesInvalide = [];

            for($i = 0; $i<count($characters); $i++){
                for($j = 0; $j<count($characters[$i]->{"personnagesInvalide"}); $j++){
                    array_push($personnagesInvalide, $characters[$i]->{"personnagesInvalide"}[$j]);
                }
            }

            $dataAnimeAutre = count($characters)+1;
            // dd($characters);

            return $this->render('battlenet/personnages.html.twig', [
                'personnages'=> $characters, 
                'deconnexion'=>"https://battle.net/login/logout?redirect_uri=$urlRedirect",
                'token'=>$token,
                'personnageInvalide' => $personnagesInvalide,
                'dataAnimeAutre' => $dataAnimeAutre
               
    
            ]);
        }
      
     }
}

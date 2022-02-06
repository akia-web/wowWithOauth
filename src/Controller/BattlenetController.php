<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BattlenetController extends AbstractController
{

 

    #[Route('/bnet', name: 'battlenet')]
    public function index(): Response
    {
        $urlRedirect = urldecode("http://127.0.0.1:8000/battlenetConnexion");
        $bnetId="53ba833be16c451caeb70b0f1f689104";

        $lien = "https://eu.battle.net/oauth/authorize?client_id=$bnetId&response_type=code&redirect_uri=$urlRedirect&scope=wow.profile";


        return $this->render('battlenet/index.html.twig', [
            'controller_name' => 'BattlenetController',
            'lien'=>$lien, 
            'personnages'=>""
        ]);
    }



    #[Route('/battlenetConnexion', name: 'battlenetConnexion')]
    public function bnetConnexion(): Response
    {
        $bnetId="53ba833be16c451caeb70b0f1f689104";
        $bnetSecret="fDORSzBYE3O38OaWPNQVu2VG6oaCpkgg";
        $urlRedirect = urlencode("http://127.0.0.1:8000/battlenetConnexion");
        $code = $_GET['code'];
               
           
        //---------------------- get token -----------------------------------------
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
        
                )
            );
        $context = stream_context_create($opts);
        $token = json_decode(file_get_contents("https://eu.battle.net/oauth/token?code=$code&redirect_uri=$urlRedirect&client_id=$bnetId&client_secret=$bnetSecret&grant_type=authorization_code&scope=wow.profile", false, $context))->{"access_token"};
        setcookie("bnetToken", $token);


        // -------------------- userInfo--------------------------------------------
        $getInfo= json_decode(file_get_contents("https://eu.battle.net/oauth/userinfo?access_token=$token"))->{'battletag'};
        setcookie("battleTag", $getInfo);

        return $this->redirect($this->generateUrl('personnages'));
           
    }



    #[Route('/deconnexion', name: 'deconnexion')]
    public function deconnexion()
    {          
        //suppression des cookies
        setcookie("bnetToken", NULL, -1);
        setcookie("battleTag", NULL, -1);

       $lien = "https://battle.net/login/logout";

       return $this->render('battlenet/logout.html.twig',[
           'lien'=>$lien

       ]);
        
    }


  


}

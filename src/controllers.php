<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

$app->match('/', function() use ($app) {
    $app['session']->setFlash('warning', 'Warning flash message');
    $app['session']->setFlash('info', 'Info flash message');
    $app['session']->setFlash('success', 'Success flash message');
    $app['session']->setFlash('error', 'Error flash message');

    return $app['twig']->render('index.html.twig');
})->bind('homepage');

$app->match('/login', function() use ($app) {

    $form = $app['form.factory']->createBuilder('form')
        ->add('email', 'email', array(
            'label'       => 'Email',
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email(),
            ),
        ))
        ->add('password', 'password', array(
            'label'       => 'Password',
            'constraints' => array(
                new Assert\NotBlank(),
            ),
        ))
        ->getForm()
    ;

    if ('POST' === $app['request']->getMethod()) {
        $form->bindRequest($app['request']);

        if ($form->isValid()) {

            $email    = $form->get('email')->getData();
            $password = $form->get('password')->getData();

            if ('email@example.com' == $email && 'password' == $password) {
                $app['session']->set('user', array(
                    'email' => $email,
                ));

                $app['session']->setFlash('notice', 'You are now connected');

                return $app->redirect($app['url_generator']->generate('homepage'));
            }

            $form->addError(new FormError('Email / password does not match (email@example.com / password)'));
        }
    }

    return $app['twig']->render('login.html.twig', array('form' => $form->createView()));
})->bind('login');

$app->match('/form', function() use ($app) {

    $builder = $app['form.factory']->createBuilder('form');
    $choices = array('choice a', 'choice b', 'choice c');

    $form = $builder
        ->add(
            $builder->create('sub-form', 'form')
                ->add('subformemail1', 'email', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                    'attr'        => array('placeholder' => 'email constraints'),
                    'label'       => 'A custome label : ',
                ))
                ->add('subformtext1', 'text')
        )
        ->add('text1', 'text', array(
            'constraints' => new Assert\NotBlank(),
            'attr'        => array('placeholder' => 'not blank constraints')
        ))
        ->add('text2', 'text', array('attr' => array('class' => 'span1', 'placeholder' => '.span1')))
        ->add('text3', 'text', array('attr' => array('class' => 'span2', 'placeholder' => '.span2')))
        ->add('text4', 'text', array('attr' => array('class' => 'span3', 'placeholder' => '.span3')))
        ->add('text5', 'text', array('attr' => array('class' => 'span4', 'placeholder' => '.span4')))
        ->add('text6', 'text', array('attr' => array('class' => 'span5', 'placeholder' => '.span5')))
        ->add('text8', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'disabled field')))
        ->add('textarea', 'textarea')
        ->add('email', 'email')
        ->add('integer', 'integer')
        ->add('money', 'money')
        ->add('number', 'number')
        ->add('password', 'password')
        ->add('percent', 'percent')
        ->add('search', 'search')
        ->add('url', 'url')
        ->add('choice1', 'choice',  array(
            'choices'  => $choices,
            'multiple' => true,
            'expanded' => true
        ))
        ->add('choice2', 'choice',  array(
            'choices'  => $choices,
            'multiple' => false,
            'expanded' => true
        ))
        ->add('choice3', 'choice',  array(
            'choices'  => $choices,
            'multiple' => true,
            'expanded' => false
        ))
        ->add('choice4', 'choice',  array(
            'choices'  => $choices,
            'multiple' => false,
            'expanded' => false
        ))
        ->add('country', 'country')
        ->add('language', 'language')
        ->add('locale', 'locale')
        ->add('timezone', 'timezone')
        ->add('date', 'date')
        ->add('datetime', 'datetime')
        ->add('time', 'time')
        ->add('birthday', 'birthday')
        ->add('checkbox', 'checkbox')
        ->add('file', 'file')
        ->add('radio', 'radio')
        ->add('password_repeated', 'repeated', array(
            'type'            => 'password',
            'invalid_message' => 'The password fields must match.',
            'options'         => array('required' => true),
            'first_options'   => array('label' => 'Password'),
            'second_options'  => array('label' => 'Repeat Password'),
        ))
        ->getForm()
    ;

    if ('POST' === $app['request']->getMethod()) {
        $form->bindRequest($app['request']);
        if ($form->isValid()) {
            $app['session']->setFlash('success', 'The form is valid');
        } else {
            $form->addError(new FormError('This is a global error'));
            $app['session']->setFlash('info', 'The form is bind, but not valid');
        }
    }

    return $app['twig']->render('form.html.twig', array('form' => $form->createView()));
})->bind('form');

$app->match('/logout', function() use ($app) {
    $app['session']->clear();

    return $app->redirect($app['url_generator']->generate('homepage'));
})->bind('logout');

$app->get('/page-with-cache', function() use ($app) {
    $response = new Response($app['twig']->render('page-with-cache.html.twig', array('date' => date('Y-M-d h:i:s'))));
    $response->setTtl(10);

    return $response;
})->bind('page_with_cache');

$app->get('/twitter', function() use ($app) {
    
    define('KEY', '58y4dIRkRXGEpsiyzbTfQ');
    define('SECRET', 'JAPw43fw8rowMDYLYR2IIRfT9xUda0TkeIaOU7a3I');
    define('TOKEN', '95092930-g8tMb6bf4T040LNrKGNoiVNjQ6IJAAvyuRaYsz9oD');
    define('TOKEN_SECRET', '6p5FLT6WspplnviOtOH3qdNgvvESIslAZGOPtsCxCsA');

    try {
        
        $twitterObj = new EpiTwitter(KEY, SECRET, TOKEN, TOKEN_SECRET); 

        $trends = $twitterObj->get('/trends/place.json', array('id' => 23424975));  
        
        $results = array();
        foreach($trends->response[0]['trends'] as $trend) {
            $search = $twitterObj->get(
                '/search/tweets.json', 
                array('q' => $trend['query']));
            //print '<pre>';
            //print_r($search->response['statuses'][0]['text']);die;
            $results[] = $search->response['statuses'][0]['text'];
        }
        
        //print '<pre>';print_r($trends->response[0]['trends']); print '</pre>';die;

    } catch (Exception $e) {
        print '<pre>';
        print $e->getCode();
        print $e->getMessage();
        die;
        $app['session']->setFlash('error', $e->getMessage());

    }
    return $app['twig']->render('twitter.html.twig', array('trends' => $results));
    
})->bind('twitter');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

return $app;

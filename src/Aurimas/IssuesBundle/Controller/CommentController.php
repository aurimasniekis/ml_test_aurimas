<?php

namespace Aurimas\IssuesBundle\Controller;

use Aurimas\IssuesBundle\Form\CommentType;
use Aurimas\IssuesBundle\Model\CommentModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommentController
 * @package Aurimas\IssuesBundle\Controller
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class CommentController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Github\Exception\MissingArgumentException
     */
    public function createAction(Request $request)
    {
        $owner = $request->get('owner');
        $name = $request->get('name');
        $issue = $request->get('issue');

        $commentModel = new CommentModel();
        $form = $this->createForm(new CommentType(), $commentModel);

        $form->handleRequest($request);

        $client = $this->get('aurimas_github.authenticated.client')->getClient();

        $client->issue()->comments()->create($owner, $name, $issue, $commentModel->saveData());

        return $this->redirectToRoute(
            'aurimas_issue_show',
            [
                'owner' => $owner,
                'name' => $name,
                'id' => $issue
            ]
        );
    }


    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Github\Exception\MissingArgumentException
     */
    public function updateAction(Request $request)
    {
        $owner = $request->get('owner');
        $name = $request->get('name');
        $issue = $request->get('issue');
        $id = $request->get('id');


        $commentModel = new CommentModel();
        $form = $this->createForm(new CommentType(), $commentModel);

        $form->handleRequest($request);

        $client = $this->get('aurimas_github.authenticated.client')->getClient();

        $client->issue()->comments()->update($owner, $name, $id, $commentModel->saveData());

        return $this->redirectToRoute(
            'aurimas_issue_show',
            [
                'owner' => $owner,
                'name' => $name,
                'id' => $issue
            ]
        );
    }
}

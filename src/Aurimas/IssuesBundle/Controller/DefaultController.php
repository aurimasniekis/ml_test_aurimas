<?php

namespace Aurimas\IssuesBundle\Controller;

use Aurimas\IssuesBundle\Form\CommentType;
use Aurimas\IssuesBundle\Form\IssueType;
use Aurimas\IssuesBundle\Model\CommentModel;
use Aurimas\IssuesBundle\Model\IssueModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param string $owner
     * @param string $name
     * @return Response
     */
    public function indexAction($owner, $name)
    {
        $client = $this->get('aurimas_github.authenticated.client')->getClient();

        $repository = $client->repo()->show($owner, $name);
        $issues = $client->issues()->all(
            $owner,
            $name,
            [
                'filter' => 'all',
                'state' => 'all'
            ]
        );

        return $this->render(
            'AurimasIssuesBundle:Default:index.html.twig',
            [
                'issues' => $issues,
                'repository' => $repository
            ]
        );
    }

    /**
     * @param string $owner
     * @param string $name
     * @return Response
     */
    public function newAction($owner, $name)
    {
        $client = $this->get('aurimas_github.authenticated.client')->getClient();
        $repository = $client->repo()->show($owner, $name);

        $issueModel = new IssueModel();
        $form = $this->createForm(new IssueType(), $issueModel, [
            'method' => 'POST',
            'action' => $this->generateUrl(
                'aurimas_issue_create',
                [
                    'owner' => $owner,
                    'name' => $name
                ]
            )
        ]);

        return $this->render(
            'AurimasIssuesBundle:Default:new.html.twig',
            [
                'form' => $form->createView(),
                'repository' => $repository
            ]
        );
    }

    public function createAction(Request $request)
    {
        $owner = $request->get('owner');
        $name = $request->get('name');

        $issueModel = new IssueModel();
        $form = $this->createForm(new IssueType(), $issueModel);
        $form->handleRequest($request);

        $client = $this->get('aurimas_github.authenticated.client')->getClient();
        $issue = $client->issues()->create($owner, $name, $issueModel->saveData());

        return $this->redirectToRoute(
            'aurimas_issue_show',
            [
                'owner' => $owner,
                'name' => $name,
                'id' => $issue['number']
            ]
        );
    }

    /**
     * @param string $owner
     * @param string $name
     * @param int $id
     * @return Response
     */
    public function showAction($owner, $name, $id)
    {
        $client = $this->get('aurimas_github.authenticated.client')->getClient();

        $repository = $client->repo()->show($owner, $name);

        $issue = $client->issue()->show($owner, $name, $id);
        $events = $client->issue()->events()->all($owner, $name, $id);
        $comments = $client->issue()->comments()->all($owner, $name, $id);

        $items = $this->buildIssueEventList($events, $comments, $owner, $name, $issue['number']);

        return $this->render(
            'AurimasIssuesBundle:Default:show.html.twig',
            [
                'issue' => $issue,
                'items' => $items,
                'repository' => $repository,
                'newCommentForm' => $this->buildNewCommentForm($owner, $name, $issue['number']),
                'issueEditForm' => $this->buildIssueEditForm($issue, $owner, $name, $id),
            ]
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateAction(Request $request)
    {
        $owner = $request->get('owner');
        $name = $request->get('name');
        $issue = $request->get('id');

        $issueModel = new IssueModel();
        $form = $this->createForm(new IssueType(), $issueModel);

        $form->handleRequest($request);

        $client = $this->get('aurimas_github.authenticated.client')->getClient();
        $client->issue()->update($owner, $name, $issue, $issueModel->saveData());

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
     * @param string $owner
     * @param string $name
     * @param string $id
     * @return RedirectResponse
     */
    public function closeAction($owner, $name, $id)
    {
        $client = $this->get('aurimas_github.authenticated.client')->getClient();
        $client->issue()->update($owner, $name, $id, ['state' => 'closed']);

        return $this->redirectToRoute(
            'aurimas_issue_show',
            [
                'owner' => $owner,
                'name' => $name,
                'id' => $id
            ]
        );
    }

    /**
     * @param array $events
     * @param array $comments
     * @return array
     */
    protected function buildIssueEventList($events, $comments, $owner, $repository, $issue)
    {
        $items = [];

        foreach ($events as $event) {
            $item = [];
            $item['type'] = 'event';
            $item['item'] = $event;

            $items[$event['created_at']] = $item;
        }

        foreach ($comments as $comment) {
            $item = [];
            $item['type'] = 'comment';
            $item['item'] = $comment;
            $item['form'] = $this->buildCommentEditForm($comment, $owner, $repository, $issue);

            $items[$comment['created_at']] = $item;
        }

        ksort($items);

        $items = array_values($items);

        return $items;
    }

    /**
     * @param array $comment
     * @return \Symfony\Component\Form\FormView
     */
    protected function buildCommentEditForm($comment, $owner, $repository, $issue)
    {
        /** @var CommentModel $commentModel */
        $commentModel = CommentModel::loadFromResponse($comment);
        $form = $this->createForm(new CommentType(), $commentModel, [
            'action' => $this->generateUrl(
                'aurimas_issue_comments_update',
                [
                    'owner' => $owner,
                    'name' => $repository,
                    'issue' => $issue,
                    'id' => $commentModel->getId()
                ]
            ),
            'method' => 'POST'
        ]);

        return $form->createView();
    }

    /**
     * @return \Symfony\Component\Form\FormView
     */
    protected function buildNewCommentForm($owner, $repository, $issue)
    {
        $commentModel = new CommentModel();
        $form = $this->createForm(new CommentType(), $commentModel, [
            'action' => $this->generateUrl(
                'aurimas_issue_comments_new',
                [
                    'owner' => $owner,
                    'name' => $repository,
                    'issue' => $issue
                ]
            ),
            'method' => 'POST'
        ]);

        return $form->createView();
    }

    /**
     * @param string $issue
     * @param string $owner
     * @param string $repository
     * @param string $issueId
     * @return \Symfony\Component\Form\FormView
     */
    protected function buildIssueEditForm($issue, $owner, $repository, $issueId)
    {
        $issueModel = IssueModel::loadFromResponse($issue);
        $form = $this->createForm(new IssueType(), $issueModel, [
            'action' => $this->generateUrl(
                'aurimas_issue_update',
                [
                    'owner' => $owner,
                    'name' => $repository,
                    'id' => $issueId
                ]
            )
        ]);

        return $form->createView();
    }
}

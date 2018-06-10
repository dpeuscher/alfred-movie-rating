<?php

namespace Dpeuscher\MovieRating\Command;

use Dpeuscher\AlfredSymfonyTools\Alfred\WorkflowResult;
use Dpeuscher\AlfredSymfonyTools\CommandExtension\AlfredInteractiveContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  user.workflow.CA05BB9D-747B-4205-B020-FD36F02B7EBD
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class MovieRatingCommand extends AlfredInteractiveContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('movie-rating')
            ->addArgument('category', InputArgument::OPTIONAL)
            ->addArgument('stars', InputArgument::OPTIONAL)
            ->addArgument('movie', InputArgument::OPTIONAL + InputArgument::IS_ARRAY);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $stars = $this->getContainer()->getParameter('stars');
        $categories = $this->getContainer()->getParameter('categories');
        $filename = $this->getContainer()->getParameter('filename');
        $sheetname = $this->getContainer()->getParameter('sheetname');
        $tablename = $this->getContainer()->getParameter('tablename');

        $this->addArgumentsAllowedValues('stars', $stars);
        $this->addArgumentsAllowedValues('category', $categories);

        $this->addInputHandler(['category', 'stars'], function () {
            $workflow = new WorkflowResult();
            $workflow->setValid(false);
            $workflow->setTitle('Insert movie name to rate');
            return [$workflow];
        });

        $showQuestions = function ($arguments) use ($categories, $filename, $sheetname, $tablename) {
            $movie = $arguments['movie'];
            $category = $arguments['category'];
            $stars = $arguments['stars'];
            if (!is_array($stars)) {
                $result = new WorkflowResult();
                $result->setValid(true);
                $result->setTitle('Rate ' . $movie . ' - ' . $category . ': ' . $stars . ' stars');
                $result->setSubtitle('Rate ' . $movie . ' in category "' . $category . '": ' . $stars . ' stars');
                $result->setLargetype('Rate ' . $movie . ' in category "' . $category . '": ' . $stars . ' stars');
                $result->setArg(json_encode([
                    'movie'       => $movie,
                    'currentdate' => (new \DateTime())->format('d.m.Y'),
                    'category'    => $category,
                    'stars'       => $stars,
                ]));
                $this->workflowHelper->variable('movie', $movie);
                $this->workflowHelper->variable('currentdate', (new \DateTime())->format('d.m.Y'));
                $this->workflowHelper->variable('category', iconv("ISO-8859-1", "UTF-8", utf8_decode($category)));
                $this->workflowHelper->variable('stars', $stars);
                $this->workflowHelper->variable('filename', $filename ?: null);
                $this->workflowHelper->variable('sheetname', $sheetname ?: null);
                $this->workflowHelper->variable('tablename', $tablename ?: null);

                return [$result];
            }
        };
        $this->addInputHandler(['category', 'stars', 'movie'], $showQuestions);
    }
}

<?php

namespace SourceBroker\Translatr\Controller;

use SourceBroker\Translatr\Domain\Model\Dto\BeLabelDemand;
use SourceBroker\Translatr\Domain\Repository\LabelRepository;
use SourceBroker\Translatr\Domain\Repository\LanguageRepository;
use SourceBroker\Translatr\Utility\LanguageUtility;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\PageRenderer;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * LabelController
 */
class LabelController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * labelRepository
     *
     * @var LabelRepository
     */
    protected $labelRepository = null;

    /**
     * @var LanguageRepository
     */
    protected $languageRepository = null;

    /**
     * @param LabelRepository $labelRepository
     */
    public function injectLabelRepository(LabelRepository $labelRepository)
    {
        $this->labelRepository = $labelRepository;
    }

    /**
     * @param LanguageRepository $languageRepository
     */
    public function injectLanguageRepository(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param \SourceBroker\Translatr\Domain\Model\Dto\BeLabelDemand|null $demand
     *
     * @return void
     *
     * @ignorevalidation $demand
     */
    public function listAction(
        \SourceBroker\Translatr\Domain\Model\Dto\BeLabelDemand $demand = null
    ) {
        if (is_null($demand)) {
            $demand = $this->objectManager->get(BeLabelDemand::class);
        }

        if ($demand->getExtension()) {
            $this->labelRepository->indexExtensionLabels($demand->getExtension());
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/AjaxDataHandler');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Translatr/Translatr');

        $pageRenderer->addRequireJsConfiguration([
                'paths' => [
                    'select2' => '../typo3conf/ext/translatr/Resources/Public/JavaScript/jquery.select2/dist/js/select2',
                ]
            ]
        );

        $this->view->assignMultiple([
            'labels' => $this->labelRepository->findDemandedForBe($demand),
            'extensions' => $this->labelRepository->getExtensionsItems(),
            'languages' => LanguageUtility::getAvailableLanguages(),
            'demand' => $demand,
            'moduleToken' => $this->getToken(),
            'id' => GeneralUtility::_GET('id'),
        ]);
    }

    /**
     * Get a CSRF token
     *
     * @return string
     */
    protected function getToken()
    {
        return FormProtectionFactory::get()
            ->generateToken('moduleCall', 'web_TranslatrTranslate');
    }
}
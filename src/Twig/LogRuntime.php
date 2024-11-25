<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ChoiceList;
use App\Entity\Log;
use App\Entity\Photo;
use App\Entity\Wish;
use App\Enum\LogTypeEnum;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class LogRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router
    ) {
    }

    public function getLogMessages(Log $log): array
    {
        $messages = [];

        $explodedNamespace = explode('\\', $log->getObjectClass());
        $class = array_pop($explodedNamespace);
        $class = strtolower($class);
        if ('tagcategory' === $class) {
            $class = 'tag_category';
        } elseif ('choicelist' === $class) {
            $class = 'choice_list';
        }

        $objectLabel = $log->getObjectLabel();

        switch ($log->getType()) {
            case LogTypeEnum::TYPE_CREATE:
                if ($log->isObjectDeleted()) {
                    $label = "<strong class='deleted'>{$objectLabel}</strong>";
                } elseif (\in_array($log->getObjectClass(), [Wish::class, Photo::class, ChoiceList::class])) {
                    $label = "<strong>{$objectLabel}</strong>";
                } else {
                    $route = $this->router->generate('app_' . $class . '_show', ['id' => $log->getObjectId()]);
                    $label = "<strong><a href='{$route}'>{$objectLabel}</a></strong>";
                }

                $messages[] = $this->translator->trans('log.' . $class . '.created', ['label' => $label]);
                break;
            case LogTypeEnum::TYPE_DELETE:
                $label = "<strong class='deleted'>{$objectLabel}</strong>";
                $messages[] = $this->translator->trans('log.' . $class . '.deleted', ['label' => $label]);
                break;
            default:
                break;
        }

        return $messages;
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ValidatorController extends AbstractController
{
    private const FORM_NAMESPACE = 'App\Form\%s';

    /**
     * @Route("/validate", name="validate_index")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validate(Request $request)
    {
        parse_str($request->getContent(), $data);
        $formName = $this->dashesToCamelCase(array_keys($data)[0], true);

        $formFQCN = sprintf(self::FORM_NAMESPACE, $formName);

        if (!class_exists($formFQCN)) {
            return new JsonResponse([
                'valid' => true,
            ]);
        }

        $form = $this->createForm($formFQCN);

        $dataClassFQCN = $form->getConfig()->getDataClass();
        $dataClass = new $dataClassFQCN();

        $form->setData($dataClass);
        $form->submit(array_values($data)[0]);

        $errors = $this->getErrorMessages(array_keys($data)[0], $form);

        if (0 === count($errors)) {
            return new JsonResponse([
                'valid' => true,
            ]);
        } else {
            return new JsonResponse([
                'valid' => false,
                'errors' => $errors,
            ]);
        }
    }

    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    private function getErrorMessages(string $keyPath, FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            if (!$form->isRoot()) {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[sprintf('%s_%s', $keyPath, $child->getName())] = $this->getErrorMessages($keyPath, $child);
            }
        }

        return $errors;
    }
}

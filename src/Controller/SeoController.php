<?php

namespace App\Controller;

use App\Service\SeoGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class SeoController extends AbstractController
{
    #[Route('/api/seo/generate', name: 'api_seo_generate', methods: ['POST'])]
    public function generate(Request $request, SeoGenerator $generator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['keyword'])) {
            return $this->json(['error' => 'keyword is required'], 400);
        }

        $keyword = is_string($data['keyword']) ? trim($data['keyword']) : '';

        if ($keyword === '') {
            return $this->json(['error' => 'keyword must be a non-empty string'], 400);
        }

        $result = $generator->generate($keyword);

        return $this->json([
            'request_id' => Uuid::v4()->toRfc4122(),
            'rules_version' => '1.0.0',
            'title' => $result['title'],
            'meta_description' => $result['meta_description'],
            'warnings' => $result['warnings'],
        ]);
    }
}
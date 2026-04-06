<?php

function getPreviewUrl(string $path = null): ?string
{
  if (!$path) return null;

  // Ambil jenis dari nama file, misalnya: str_123456.pdf → str
  $jenis = explode('_', $path)[0] ?? 'dokumen';
  $relativePath = "uploads/pegawai/$jenis/$path";
  $fullPath = realpath(__DIR__ . "/../../public/$relativePath");

  if (!$fullPath || !file_exists($fullPath)) return null;

  return BASE_URL . "/$relativePath";
}

function getPreviewIcon(string $path = null): string
{
  if (!$path) return BASE_URL . '/assets/icons/file-icon.png';

  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

  return match ($ext) {
    'pdf' => BASE_URL . '/assets/icons/pdf-icon.png',
    'jpg', 'jpeg', 'png' => getPreviewUrl($path) ?? BASE_URL . '/assets/icons/image-icon.png',
    default => BASE_URL . '/assets/icons/file-icon.png'
  };
}

function renderPreviewLink(string $label, string $path = null): string
{
  $url = getPreviewUrl($path);
  if (!$url) {
    return "<span class='text-muted'>$label belum tersedia</span>";
  }

  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  $icon = match ($ext) {
    'pdf' => "<i class='fas fa-file-pdf text-danger'></i>",
    'jpg', 'jpeg', 'png' => "<i class='fas fa-image text-primary'></i>",
    default => "<i class='fas fa-file text-secondary'></i>"
  };

  // ambil nama file saja
  $filename = htmlspecialchars(basename($path));

  return "<div class='mb-2'><strong>$label:</strong> <a href='$url' target='_blank'>$icon $filename</a></div>";
}

function renderPreviewImage(string $label, string $path = null, int $height = 60): string
{
  $url = getPreviewUrl($path);
  if (!$url) {
    return "<div class='mb-2'><strong>$label:</strong> <span class='text-muted'>Belum ada gambar</span></div>";
  }

  return "<div class='mb-2'><strong>$label:</strong><br><img src='$url' alt='$label' style='height: {$height}px; border-radius: 4px;'>";
}
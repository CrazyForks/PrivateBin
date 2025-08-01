<?php
/**
 * This file is part of Jdenticon for PHP.
 * https://github.com/dmester/jdenticon-php/
 * 
 * Copyright (c) 2025 Daniel Mester Pirttijärvi
 * 
 * For full license information, please see the LICENSE file that was 
 * distributed with this source code.
 */

namespace Jdenticon\Rendering;

use Jdenticon\Color;

/**
 * Renders icons as SVG paths.
 */
class SvgRenderer extends AbstractRenderer
{
    /** @var array<string, SvgPath> */
    private array $pathsByColor = [];
    private ?SvgPath $path = null;
    private int $width;
    private int $height;

    /**
     * Creates a new SvgRenderer.
     *
     * @param int $width The width of the icon in pixels.
     * @param int $height The height of the icon in pixels.
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }
    
    /**
     * Gets the MIME type of the renderer output.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'image/svg+xml';
    }

    /**
     * Adds a circle without translating its coordinates.
     *
     * @param float $x The x-coordinate of the bounding rectangle 
     *      upper-left corner.
     * @param float $y The y-coordinate of the bounding rectangle 
     *      upper-left corner.
     * @param float $size The size of the bounding rectangle.
     * @param bool $counterClockwise If true the circle will be drawn 
     *      counter clockwise.
     */
    protected function addCircleNoTransform(float $x, float $y, float $size, bool $counterClockwise): void
    {
        $this->path->addCircle($x, $y, $size, $counterClockwise);
    }

    /**
     * Adds a polygon without translating its coordinates.
     *
     * @param array<Point> $points An array of the points that the polygon consists of.
     */
    protected function addPolygonNoTransform(array $points): void
    {
        $this->path->addPolygon($points);
    }

    /**
     * Begins a new shape. The shape should be ended with a call to endShape.
     *
     * @param \Jdenticon\Color $color The color of the shape.
     */
    public function beginShape(Color $color): void
    {
        $colorString = $color->toHexString(6);
        
        if (isset($this->pathsByColor[$colorString])) {
            $this->path = $this->pathsByColor[$colorString];
        } else {
            $this->path = new SvgPath();
            $this->pathsByColor[$colorString] = $this->path;
        }
    }
    
    /**
     * Ends the currently drawn shape.
     */
    public function endShape(): void
    {
    }
    
    /**
     * Generates an SVG string of the renderer output.
     *
     * @param bool $fragment If true an SVG string without the root svg element 
     *      will be rendered.
     * @return string
     */
    public function getData(bool $fragment = false): string
    {
        $svg = '';
        $widthAsString = number_format($this->width, 0, '.', '');
        $heightAsString = number_format($this->height, 0, '.', '');
        
        if (!$fragment) {
            $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' .
                $widthAsString .'" height="'. $heightAsString .'" viewBox="0 0 '.
                $widthAsString .' '. $heightAsString .
                '" preserveAspectRatio="xMidYMid meet">';
        }

        if ($this->backgroundColor->a > 0) {
            $opacity = (float)$this->backgroundColor->a / 255;
            $svg .= '<rect fill="'. $this->backgroundColor->toHexString(6) .
                '" fill-opacity="'. number_format($opacity, 2, '.', '').
                '" x="0" y="0" width="'. $widthAsString .'" height="'. 
                $heightAsString .'"/>';
        }
        
        foreach ($this->pathsByColor as $color => $path) {
            $svg .= "<path fill=\"$color\" d=\"$path\"/>";
        }

        if (!$fragment) {
            $svg .= '</svg>';
        }
        
        return $svg;
    }
}

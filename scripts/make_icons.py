"""Render the BEZ "B" logo to favicon.ico, apple-touch-icon.png and logo-512.png.

The shape matches public/assets/img/logo.svg - change both if you edit the logo.
Requires Pillow (pip install pillow). Run: python scripts/make_icons.py
"""
import sys
from pathlib import Path
from PIL import Image, ImageDraw

out = Path(sys.argv[1]) if len(sys.argv) > 1 else Path(__file__).resolve().parent.parent / "public"
S = 32          # design grid
R = 1024        # render size (supersampled)
k = R / S


def gradient(size):
    """Diagonal gradient #3b82f6 -> #8b5cf6, top-left to bottom-right."""
    a, b = (0x3B, 0x82, 0xF6), (0x8B, 0x5C, 0xF6)
    img = Image.new("RGB", (size, size))
    px = img.load()
    for y in range(size):
        for x in range(size):
            t = (x + y) / (2 * (size - 1))
            px[x, y] = tuple(round(a[i] + (b[i] - a[i]) * t) for i in range(3))
    return img


def b_mask():
    m = Image.new("L", (R, R), 0)
    d = ImageDraw.Draw(m)
    sc = lambda *v: [c * k for c in v]
    circle = lambda cx, cy, r, fill: d.ellipse(sc(cx - r, cy - r, cx + r, cy + r), fill=fill)
    # outer shape
    d.rectangle(sc(9, 7, 17, 15.5), fill=255)
    circle(17, 11.25, 4.25, 255)
    d.rectangle(sc(9, 15.5, 18, 25), fill=255)
    circle(18, 20.25, 4.75, 255)
    # holes
    d.rectangle(sc(12.5, 10, 16.75, 13.5), fill=0)
    circle(16.75, 11.75, 1.75, 0)
    d.rectangle(sc(12.5, 17.5, 17.75, 22), fill=0)
    circle(17.75, 19.75, 2.25, 0)
    return m


def render(rounded=True):
    bg = gradient(R).convert("RGBA")
    bg.paste(Image.new("RGBA", (R, R), "white"), (0, 0), b_mask())
    if rounded:
        shape = Image.new("L", (R, R), 0)
        ImageDraw.Draw(shape).rounded_rectangle((0, 0, R - 1, R - 1), radius=7 * k, fill=255)
        bg.putalpha(shape)
    return bg


icon = render(rounded=True)
icon.save(out / "favicon.ico", sizes=[(16, 16), (32, 32), (48, 48)])
icon.resize((512, 512), Image.LANCZOS).save(out / "assets/img/logo-512.png")
# iOS adds its own rounded corners, so the touch icon is a full square.
render(rounded=False).resize((180, 180), Image.LANCZOS).save(out / "apple-touch-icon.png")
print("done")

"""Une las portadas entregadas con los cuerpos completos de ambos manuales."""

from pathlib import Path
import sys

from pypdf import PdfReader, PdfWriter


ROOT = Path(__file__).resolve().parents[2]
OUTPUT_DIR = ROOT / "docs" / "manuales"
DEFAULT_USER_COVER = Path(
    r"C:\Users\Usuario\Desktop\Sistemas\3er año\IngdeSoftware3\proyectoo"
    r"\Manual_de_Usuario_ISFT_38_con_portada.pdf"
)
DEFAULT_TECH_COVER = Path(
    r"C:\Users\Usuario\Desktop\Sistemas\3er año\IngdeSoftware3\proyectoo"
    r"\Manual_Tecnico_ISFT_38_con_portada.pdf"
)


def assemble(cover_source: Path, body_source: Path, output: Path, title: str) -> None:
    if not cover_source.is_file():
        raise FileNotFoundError(f"No se encontró el PDF con la portada: {cover_source}")
    if not body_source.is_file():
        raise FileNotFoundError(f"No se encontró el cuerpo completo: {body_source}")

    cover = PdfReader(str(cover_source))
    body = PdfReader(str(body_source))
    if not cover.pages:
        raise ValueError(f"El PDF de portada no contiene páginas: {cover_source}")

    writer = PdfWriter()
    writer.add_page(cover.pages[0])
    for page in body.pages:
        writer.add_page(page)
    writer.add_metadata({
        "/Title": title,
        "/Author": "Francisco Herrera",
        "/Subject": "Sistema de Asistencia del ISFT N.º 38",
        "/Creator": "Generador de documentación del proyecto",
    })
    with output.open("wb") as stream:
        writer.write(stream)


def main() -> None:
    user_cover = Path(sys.argv[1]) if len(sys.argv) > 1 else DEFAULT_USER_COVER
    tech_cover = Path(sys.argv[2]) if len(sys.argv) > 2 else DEFAULT_TECH_COVER
    assemble(
        user_cover,
        ROOT / "docs" / "pdf" / "Manual_Usuario_Asistencia.pdf",
        OUTPUT_DIR / "Manual_de_Usuario_ISFT_38_final.pdf",
        "Manual de Usuario — Sistema de Asistencia ISFT N.º 38",
    )
    assemble(
        tech_cover,
        ROOT / "docs" / "pdf" / "Manual_Desarrollador_Asistencia.pdf",
        OUTPUT_DIR / "Manual_Tecnico_ISFT_38_final.pdf",
        "Manual del Desarrollador — Sistema de Asistencia ISFT N.º 38",
    )
    print(f"Manuales finales generados en {OUTPUT_DIR}")


if __name__ == "__main__":
    main()

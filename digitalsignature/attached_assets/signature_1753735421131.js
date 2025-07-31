const canvas = document.getElementById('signature-pad');
if (canvas) {
  const signaturePad = new SignaturePad(canvas);
  document.getElementById('signature-clear').addEventListener('click', () => signaturePad.clear());
  document.querySelector('form').addEventListener('submit', (e) => {
    if (!signaturePad.isEmpty()) {
      document.getElementById('signature_data').value = signaturePad.toDataURL('image/png');
    }
  });
}

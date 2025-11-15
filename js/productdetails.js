document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const productName = params.get('name');
  const productPrice = params.get('price');
  const productImg = params.get('img');

  if (productName) {
    document.getElementById('product-name').textContent = productName;
  }
  if (productPrice) {
    document.getElementById('product-price').textContent = productPrice;
  }
  if (productImg) {
    document.getElementById('main-image').src = productImg;
  }
});

// Click-to-copy on every <pre class="code"> (or anything with a .copy-btn).
document.querySelectorAll('pre.code').forEach((pre) => {
    if (pre.querySelector('.copy-btn')) return;
    const btn = document.createElement('button');
    btn.className = 'copy-btn';
    btn.type = 'button';
    btn.textContent = 'copy';
    btn.addEventListener('click', () => {
        const text = pre.querySelector('code')?.innerText ?? pre.innerText;
        navigator.clipboard.writeText(text.replace(/copy$/m, '').trim()).then(() => {
            btn.textContent = 'copied!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = 'copy';
                btn.classList.remove('copied');
            }, 1400);
        });
    });
    pre.appendChild(btn);
});

// Stable random rotation on library cards (no layout reflow per render).
document.querySelectorAll('.lib-card .glyph-tag').forEach((g) => {
    const r = (Math.random() * 14 - 7).toFixed(1);
    g.style.transform = `rotate(${r}deg)`;
});

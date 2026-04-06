<style>
.btn-back {
    padding: 12px 20px;
    background: #5c5c5c;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.btn-back:hover {
    background: #8a8a8a;
}
</style>

<button class="btn-back" type="button" onclick="goBack()">← Back</button>

<script>
function goBack() {
    window.location = document.referrer;
}
</script>

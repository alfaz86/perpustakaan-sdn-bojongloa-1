<style>
    @media (min-width: 1024px) {
        h1.text-footer {
            margin-left: 320px !important;
        }
        p.text-footer {
            margin-left: 160px !important;
        }
    }
    @media (max-width: 548px) {
        p.footer-page {
            bottom: 96px !important;
        }
    }
    .footer-page {
        z-index: 0 !important;
    }
    .fi-ta-ctn {
        margin-bottom: 100px !important;
    }
</style>
@if (request()->is('panel'))
    <p class="footer-page fixed left-0 w-full flex justify-center items-center text-footer" style="bottom: 64px; background-color: #fafafa !important;">Dusun Citanam RT 015 / RW 007</p>
@endif
<footer class="footer-page fixed bottom-0 left-0 w-full p-4 border-t border-gray-200 shadow flex justify-center items-center" style="background-color: #6477DB !important;">
    <h1 class="text-center text-2xl font-bold text-footer" style="color: yellow;">
        "Bacalah Bukumu, Bukalah Masa Depanmu"
    </h1>
</footer>

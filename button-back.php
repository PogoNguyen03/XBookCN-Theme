<?php
/**
 * Back Button Template
 */
?>
<style>
    
    @media (max-width: 768px) {
        /*.button-class{*/
        /*    padding-left: 0rem;*/
        /*}*/
        .back-btn-fixed {
            position: fixed;
            width: 48px;
            height: 48px;
            bottom: 2rem;
            left: 20px;
            background: #ffffff;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            z-index: 9999;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .back-btn-fixed:hover {
            background: #a1a1a1;
        }
        .back-btn-fixed svg path {
            fill: black;
            transition: fill 0.3s ease;
        }
        .back-btn-fixed:hover svg path {
            fill: white; /* đổi icon thành màu trắng khi hover */
        }
        .button-class{
            /*padding-left: 9rem;*/
        }
    }
</style>
<button class="back-btn-fixed" onclick="window.history.back()" aria-label="Quay lại">
    <!-- SVG icon mũi tên trái -->
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
    </svg>
</button>

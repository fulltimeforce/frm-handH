<style>
    @media (min-width: 1420px) {
        section.single-post {
            padding: 11.9792vw 5vw;
        }

        .post-content>*:not(:last-child) {
            margin-bottom: 1.25vw;
        }

        .post-content p,
        .post-content li,
        .post-content span,
        .post-content td,
        .post-content table,
        .post-content tr,
        .post-content a {
            font-size: .8854166667vw !important;
            letter-spacing: .053125vw !important;
            line-height: 1.2395833333vw !important;
        }

        .post-content table,
        .post-content td,
        .post-content tr {
            border: 0.052vw solid black;
        }

        .post-content td {
            padding: 0.833vw;
        }

        .post-content ul,
        .post-content ol {
            padding-left: 1.094vw;
        }

        .post-content li:not(:last-child) {
            margin-bottom: 0.417vw;
        }
    }

    .post-content td,
    .post-content table,
    .post-content tr {
        border-collapse: collapse;
    }

    @media (max-width: 1420px) {
        section.single-post {
            padding-block: 164px 76px;
        }

        .post-content>*:not(:last-child) {
            margin-bottom: 16px;
        }

        .post-content p,
        .post-content li,
        .post-content span,
        .post-content td,
        .post-content table,
        .post-content tr,
        .post-content a {
            font-size: 14.5px !important;
            letter-spacing: .4px !important;
            line-height: 1.3em !important;
        }

        .post-content table,
        .post-content td,
        .post-content tr {
            border: 1px solid black;
        }

        .post-content td {
            padding: 10px;
        }

        .post-content ul,
        .post-content ol {
            padding-left: 16px;
        }

        .post-content li:not(:last-child) {
            margin-bottom: 4.6px;
        }
    }

    .post-content p,
    .post-content li,
    .post-content span,
    .post-content td,
    .post-content table,
    .post-content tr,
    .post-content a {
        color: black;
        opacity: 0.9;
    }

    .post-content p,
    .post-content li,
    .post-content span,
    .post-content td,
    .post-content table,
    .post-content tr {
        font-family: GothamLight;
        font-weight: 300;
    }

    .post-content a,
    .post-content b,
    .post-content strong {
        font-family: GothamMedium;
        font-weight: 400;
    }

    .post-thumbnail img {
        width: 100%;
        border: 1px solid #8c6e47;
    }

    .post-content img {
        max-width: 100%;
    }

    .post-content h1,
    .post-content h2,
    .post-content h3,
    .post-content h4,
    .post-content h5,
    .post-content h6,
    .post-title {
        font-family: GoudyTitlingSemiBold;
        padding: 0;
    }
</style>

<?php get_header(); ?>

<section class="single-post">
    <div class="container">
        <?php if (has_post_thumbnail()) : ?>
            <!--<div class="post-thumbnail">
                <?php //the_post_thumbnail('full'); 
                ?>
            </div>-->
        <?php endif; ?>

        <div class="post-content">
            <h1 class="post-title"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
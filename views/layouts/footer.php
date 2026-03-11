<?php
$version = \app\commands\DeployController::getVersion();
$versionLabel = !empty($version['tag']) ? $version['tag'] : ('dev@' . substr($version['commit'], 0, 7));
$versionTitle = !empty($version['date']) ? 'Deploy: ' . $version['date'] : '';
?>
<footer class="footer footer-transparent d-print-none">
    <div class="container-xl">
        <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-lg-auto ms-lg-auto">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item"><a href="#" class="link-secondary">Documentazione</a></li>
                    <li class="list-inline-item"><a href="#" class="link-secondary">FAQ</a></li>
                    <li class="list-inline-item">
                        Sviluppato da <a href="mailto:albatros+info@robertodedomenico.it" class="link-secondary">Ing. Roberto De Domenico</a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">
                        Copyright &copy; <?= date('Y') ?>
                        <a href="." class="link-secondary">Albatros Manager</a>.
                        Tutti i diritti riservati.
                    </li>
                    <li class="list-inline-item">
                        <span class="link-secondary" title="<?= $versionTitle ?>">
                            <?= $versionLabel ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

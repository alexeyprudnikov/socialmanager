                <footer>
                    <?php echo Core::getTranslation('wechseln_zu');
                    $availableLgs = Core::getAvailableLanguages();
                    if(isset($availableLgs[Core::getInstance()->getSystemLanguage()]))
                        unset($availableLgs[Core::getInstance()->getSystemLanguage()]);
                    $index = 0;
                    foreach( $availableLgs as $lg => $name ) {
                        $index++;
                        if($index > 1) {
                            echo ',';
                        }
                        echo ' <a href="#" class="btn_SwitchLanguage" data-lg="'.$lg.'">'.$name.'</a>';
                    } ?> | &copy; <b>ap</b> <?php echo date( 'Y' ); ?>
                </footer>
            </div>
        </div>
	</body>
</html>
<style>
#root {
    position: absolute;
    top: 0; bottom: 0;
    left: 0; right: 0;
    padding-top: 62px;
}
#link {
    position: absolute;
    top: 0;
    right: 0;
    margin: 15px;
    z-index: 10000;
}
#display {
    width: 100%;
    height: 100%;
    border: 0;
    background: #eeeeee;
}
</style>

<div id="root">
    <div id="link">
        <a href="<?= $data['url'] ?>">
            <i class="fa fa-external-link-square"></i>
        </a>
    </div>
    <iframe id="display" src="<?= $data['url'] ?>"></iframe>
</div>


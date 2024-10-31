//update form fields
jQuery(document).ready(function ($) {

    $("#sendsmith-addnew-fields").click(function () {
        $("#sendsmith_admin_field_list tbody").append($("#sendsmith_admin_field_list_tpl .sendsmith-input-container:first").clone());
    });

    $("#sendsmith-addnew-tag").click(function () {
        var tpl = $("#sendsmith-tag-select-tpl tr").clone();
        var randomid = uniqid("newtag");
        $(tpl).find("select").attr("id", randomid).attr('name', "sendsmith-tags[" + randomid + "][pages][]");
        $(tpl).find(".sendsmith-tag").attr('name', "sendsmith-tags[" + randomid + "][tag]");
        $("#sendsmith_admin_tags_list tbody").append(tpl);
      //  $("#" + randomid).select2();

    });

    $("#sendsmith_admin_tags_list tbody").on("click", ".sendsmith-feild-delete-button", function () {
        $(this).closest("tr").remove();
    });
    $("#sendsmith_admin_field_list tbody").on("click", ".sendsmith-feild-delete-button", function () {
        $(this).closest("tr").remove();
    });

    //using select2 for tags
    $(".sendsmith-tags-pagelist-edit").select2();
    $(".sendsmith-role-tags").select2({
        tags: true
    });
    
    $(".sendsmith-form-tags").select2({
        tags: true
    });
    
    $("#sendsmith-add-shortcode").click(function(){
        wp.media.editor.insert('[sendsmith-form]');
    });
});

function uniqid(prefix, more_entropy) {
    if (typeof prefix === 'undefined') {
        prefix = '';
    }

    var retId;
    var formatSeed = function (seed, reqWidth) {
        seed = parseInt(seed, 10)
                .toString(16); // to hex str
        if (reqWidth < seed.length) { // so long we split
            return seed.slice(seed.length - reqWidth);
        }
        if (reqWidth > seed.length) { // so short we pad
            return Array(1 + (reqWidth - seed.length))
                    .join('0') + seed;
        }
        return seed;
    };

    // BEGIN REDUNDANT
    if (!this.php_js) {
        this.php_js = {};
    }
    // END REDUNDANT
    if (!this.php_js.uniqidSeed) { // init seed with big random int
        this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
    }
    this.php_js.uniqidSeed++;

    retId = prefix; // start with prefix, add current milliseconds hex string
    retId += formatSeed(parseInt(new Date()
            .getTime() / 1000, 10), 8);
    retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
    if (more_entropy) {
        // for more entropy we add a float lower to 10
        retId += (Math.random() * 10)
                .toFixed(8)
                .toString();
    }

    return retId;
}

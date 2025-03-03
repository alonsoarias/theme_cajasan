/**
 * Modal Manager Module.
 *
 * Este módulo gestiona la apertura y cierre de un modal con animación fade,
 * simulando el comportamiento de Bootstrap.
 *
 * @module     theme_cajasan/modal_manager
 * @copyright  2025 Cajasan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    'use strict';

    var ModalManager = function(modalSelector, triggerSelector) {
        this.$modal = $(modalSelector);
        this.$trigger = $(triggerSelector);
        this.init();
    };

    ModalManager.prototype.createBackdrop = function() {
        var $backdrop = $('<div class="modal-backdrop fade"></div>');
        $('body').append($backdrop);
        // Forzar reflow para activar la transición.
        void $backdrop[0].offsetWidth;
        $backdrop.addClass('show');
        return $backdrop;
    };

    ModalManager.prototype.openModal = function(e) {
        e.preventDefault();
        var $modal = this.$modal;
        var $backdrop = this.createBackdrop();
        $modal.css('display', 'block').hide().fadeIn(300, function() {
            $modal.addClass('show')
                  .attr('aria-modal', 'true')
                  .removeAttr('aria-hidden');
        });
        $modal.data('backdrop', $backdrop);
    };

    ModalManager.prototype.closeModal = function(e) {
        e.preventDefault();
        var $modal = this.$modal;
        var $backdrop = $modal.data('backdrop');
        $modal.removeClass('show')
              .attr('aria-hidden', 'true')
              .removeAttr('aria-modal');
        $modal.fadeOut(300, function() {
            $modal.css('display', 'none');
        });
        if ($backdrop) {
            $backdrop.removeClass('show');
            setTimeout(function() {
                $backdrop.remove();
            }, 150);
        }
    };

    ModalManager.prototype.init = function() {
        var self = this;
        if (this.$trigger.length && this.$modal.length) {
            this.$trigger.on('click', function(e) {
                self.openModal(e);
            });
            this.$modal.find('[data-dismiss="modal"]').on('click', function(e) {
                self.closeModal(e);
            });
            this.$modal.on('click', function(e) {
                if ($(e.target).is(self.$modal)) {
                    self.closeModal(e);
                }
            });
        }
    };

    return {
        /**
         * Inicializa el ModalManager.
         *
         * @param {string} modalSelector    Selector del modal.
         * @param {string} triggerSelector  Selector del disparador del modal.
         * @return {ModalManager} Instancia del ModalManager.
         */
        init: function(modalSelector, triggerSelector) {
            return new ModalManager(modalSelector, triggerSelector);
        }
    };
});

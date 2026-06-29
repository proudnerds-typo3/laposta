/**
 * Moves focus to the laposta flash message after a post-submit redirect.
 *
 * A role="alert" is not reliably announced by screen readers on a full page load
 * (it works for dynamically inserted content). After the post/redirect/get flow the
 * message is present from the start, so we move focus to it to make sure it is noticed
 * and read. The target only exists when a message was queued, so on normal page loads
 * nothing happens.
 */
class LapostaMessageFocus {
  #element

  constructor(selector) {
    this.#element = document.querySelector(selector)
    this.init()
  }

  init() {
    this.#element?.focus()
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new LapostaMessageFocus('[data-laposta-message]')
})

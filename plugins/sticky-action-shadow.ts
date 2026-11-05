import type { Directive } from "vue";

interface StickyActionShadowState {
  scrollElement: HTMLElement;
  update: () => void;
  observeTable: () => void;
  resizeObserver: ResizeObserver | null;
}

const states = new WeakMap<HTMLElement, StickyActionShadowState>();

const stickyActionShadowDirective: Directive<HTMLElement> = {
  mounted(element) {
    const scrollElement =
      element.querySelector<HTMLElement>(".datatable-scroll") ?? element;

    const update = () => {
      const maxScrollLeft = scrollElement.scrollWidth - scrollElement.clientWidth;
      const hasOverflow = maxScrollLeft > 1;

      element.classList.toggle("has-horizontal-overflow", hasOverflow);
      element.classList.toggle(
        "is-at-scroll-end",
        hasOverflow && maxScrollLeft - scrollElement.scrollLeft <= 1
      );
    };

    const resizeObserver =
      typeof ResizeObserver === "undefined" ? null : new ResizeObserver(update);
    let observedTable: HTMLTableElement | null = null;

    const observeTable = () => {
      const table = scrollElement.querySelector("table");
      if (table === observedTable) return;

      if (observedTable) resizeObserver?.unobserve(observedTable);
      observedTable = table;
      if (observedTable) resizeObserver?.observe(observedTable);
    };

    element.classList.add("sticky-action-shadow-wrapper");
    scrollElement.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
    resizeObserver?.observe(element);
    if (scrollElement !== element) resizeObserver?.observe(scrollElement);

    const state = { scrollElement, update, observeTable, resizeObserver };
    states.set(element, state);
    observeTable();
    update();
  },

  updated(element) {
    const state = states.get(element);
    state?.observeTable();
    state?.update();
  },

  unmounted(element) {
    const state = states.get(element);
    if (!state) return;

    state.scrollElement.removeEventListener("scroll", state.update);
    window.removeEventListener("resize", state.update);
    state.resizeObserver?.disconnect();
    states.delete(element);
  },
};

export default defineNuxtPlugin(({ vueApp }) => {
  vueApp.directive("sticky-action-shadow", stickyActionShadowDirective);
});

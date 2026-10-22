import { nextTick, onBeforeUnmount, onMounted, ref } from "vue";

export function useActionDropdown() {
  const activeDropdown = ref(null);
  const actionDropdownMenu = ref(null);
  let actionDropdownTrigger = null;
  let positionFrame = null;

  const closeActionDropdown = () => {
    activeDropdown.value = null;
    actionDropdownTrigger = null;
    actionDropdownMenu.value = null;

    if (positionFrame !== null) {
      window.cancelAnimationFrame(positionFrame);
      positionFrame = null;
    }
  };

  const positionActionDropdown = () => {
    const menu = actionDropdownMenu.value;
    const trigger = actionDropdownTrigger;
    if (!menu || !trigger?.isConnected) {
      closeActionDropdown();
      return;
    }

    const viewportPadding = 8;
    const gap = 6;
    const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
    const viewportHeight = document.documentElement.clientHeight || window.innerHeight;

    menu.style.setProperty("position", "fixed", "important");
    menu.style.setProperty("top", "0px", "important");
    menu.style.setProperty("left", "0px", "important");
    menu.style.setProperty("right", "auto", "important");
    menu.style.setProperty("visibility", "hidden", "important");
    menu.style.setProperty("z-index", "1200", "important");
    menu.style.maxWidth = `${viewportWidth - viewportPadding * 2}px`;
    menu.style.maxHeight = `${viewportHeight - viewportPadding * 2}px`;
    menu.style.overflowY = "auto";

    const triggerRect = trigger.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const menuWidth = Math.min(menuRect.width, viewportWidth - viewportPadding * 2);
    const menuHeight = Math.min(menu.scrollHeight, viewportHeight - viewportPadding * 2);
    const spaceBelow = viewportHeight - triggerRect.bottom - viewportPadding;
    const spaceAbove = triggerRect.top - viewportPadding;
    const openAbove = menuHeight + gap > spaceBelow && spaceAbove > spaceBelow;
    const top = openAbove
      ? triggerRect.top - menuHeight - gap
      : triggerRect.bottom + gap;
    const left = Math.max(
      viewportPadding,
      Math.min(triggerRect.right - menuWidth, viewportWidth - menuWidth - viewportPadding)
    );

    menu.style.setProperty(
      "top",
      `${Math.max(viewportPadding, Math.min(top, viewportHeight - menuHeight - viewportPadding))}px`,
      "important"
    );
    menu.style.setProperty("left", `${left}px`, "important");
    menu.style.setProperty("visibility", "visible", "important");
  };

  const setActionDropdownMenu = (element) => {
    actionDropdownMenu.value = element;
    if (!element) return;

    element.style.setProperty("position", "fixed", "important");
    element.style.setProperty("top", "0px", "important");
    element.style.setProperty("left", "0px", "important");
    element.style.setProperty("visibility", "hidden", "important");
    element.style.setProperty("z-index", "1200", "important");
  };

  const schedulePosition = () => {
    if (activeDropdown.value === null || positionFrame !== null) return;

    positionFrame = window.requestAnimationFrame(() => {
      positionFrame = null;
      positionActionDropdown();
    });
  };

  const toggleActionDropdown = async (id, event) => {
    if (activeDropdown.value === id) {
      closeActionDropdown();
      return;
    }

    activeDropdown.value = id;
    actionDropdownTrigger = event.currentTarget;
    await nextTick();
    positionActionDropdown();
  };

  const handleOutsidePointer = (event) => {
    if (activeDropdown.value === null) return;

    const path = event.composedPath();
    if (
      (actionDropdownTrigger && path.includes(actionDropdownTrigger)) ||
      (actionDropdownMenu.value && path.includes(actionDropdownMenu.value))
    ) {
      return;
    }

    closeActionDropdown();
  };

  onMounted(() => {
    document.addEventListener("pointerdown", handleOutsidePointer);
    window.addEventListener("resize", schedulePosition);
    window.addEventListener("scroll", schedulePosition, true);
  });

  onBeforeUnmount(() => {
    document.removeEventListener("pointerdown", handleOutsidePointer);
    window.removeEventListener("resize", schedulePosition);
    window.removeEventListener("scroll", schedulePosition, true);

    if (positionFrame !== null) {
      window.cancelAnimationFrame(positionFrame);
    }
  });

  return {
    activeDropdown,
    closeActionDropdown,
    setActionDropdownMenu,
    toggleActionDropdown,
  };
}


/**
 * Retrieves the sidebar state
 *
 * @return string|null
 */
export default function getSidebarState() {
  if (typeof DeskPRO_Window.appsSidebar !== 'object') { // eslint-disable-line no-undef
    return null;
  }

  const { pinned, expanded } = DeskPRO_Window.appsSidebar; // eslint-disable-line no-undef
  if (pinned) {
    return 'pinned';
  }

  return expanded ? 'expanded' : 'collapsed';
}

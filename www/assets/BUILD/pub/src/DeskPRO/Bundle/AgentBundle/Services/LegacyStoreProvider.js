import { allSnippetsSelector, allSnippetBlobsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Selectors/snippets';

class LegacyStoreProvider {
  init(store) {
    this.store = store;
  }

  getState() {
    return this.store.getState();
  }

  getSnippets() {
    return allSnippetsSelector(this.store.getState());
  }

  getSnippetBlobs() {
    return allSnippetBlobsSelector(this.store.getState());
  }
}
export default LegacyStoreProvider;

import { allSnippetsSelector, allSnippetBlobsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Selectors/snippets';

class LegacyStoreProvider {
  constructor(agentLegacyApp) {
    this.agentLegacyApp = agentLegacyApp;
  }

  getState() {
    return this.agentLegacyApp.store.getState();
  }

  getSnippets() {
    return allSnippetsSelector(this.agentLegacyApp.store.getState());
  }

  getSnippetBlobs() {
    return allSnippetBlobsSelector(this.agentLegacyApp.store.getState());
  }
}
export default LegacyStoreProvider;

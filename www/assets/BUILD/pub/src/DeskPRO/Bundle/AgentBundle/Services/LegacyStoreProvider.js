import { allSnippetsSelector, allSnippetBlobsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Selectors/snippets';

class LegacyStoreProvider {
  constructor(agentLegacyApp) {
    this.agentLegacyApp = agentLegacyApp;
  }

  getState() {
    return this.agentLegacyApp.getStore().getState();
  }

  getSnippets() {
    return allSnippetsSelector(this.agentLegacyApp.getStore().getState());
  }

  getSnippetBlobs() {
    return allSnippetBlobsSelector(this.agentLegacyApp.getStore().getState());
  }
}
export default LegacyStoreProvider;

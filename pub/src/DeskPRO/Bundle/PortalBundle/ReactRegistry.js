import DpLevelSelect from "DeskPRO/Component/ReactModule/DpLevelSelect";

export default class ReactRegistry {
  constructor(container) {
    this.container = container;
    this.register("DpLevelSelect", DpLevelSelect);
  }

  register(id, component) {
    this.container.registerFactory("ReactComponent." + id, ['container', (c) => c.invoke(DpLevelSelect)]);
  }

  get(id) {
    return this.container.get("ReactComponent." + id);
  }
}
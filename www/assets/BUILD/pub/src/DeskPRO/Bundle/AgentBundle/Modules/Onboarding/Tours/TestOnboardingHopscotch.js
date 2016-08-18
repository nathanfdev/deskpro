class TestOnboarding {
  static get() {
    return {
      id: "hello-hopscotch",
      steps: [
        {
          title: "My Header",
          content: "This is the header of my page.",
          target: "#react_dp_agent_top_bar .item.add",
          placement: "right"
        },
        {
          title: "My content",
          content: "Here is where I put my content.",
          target: document.querySelector("#content p"),
          placement: "bottom"
        }
      ]
    };
  }  
}
export default TestOnboarding;
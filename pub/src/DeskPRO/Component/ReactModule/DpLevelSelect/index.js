import createComponents from "./components.jsx";

export default function create() {
  let components = createComponents();

  return {
    components: components
  };
}
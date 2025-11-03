export class Desarrollo {
  constructor(texto = "") {
    this.texto = texto;
  }

  fromJson(data) {
    this.texto = data.texto ?? "";
  }
}
